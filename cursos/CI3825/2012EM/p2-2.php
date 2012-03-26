<?php
        // Content negotiation? #merwebo.
        // No me voy a poner a hacer una versión con los escapes de otra forma solo para gente con browsers chimbos.
        header('Content-Type:application/xhtml+xml;charset=utf-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
        <head>
                <meta http-equiv="Content-type" content="application/xhtml+xml;charset=utf-8"/>
                <title>Notas de Manuel Gómez sobre CI3825 — Otra consulta del primer proyecto</title>
                <style type="text/css">
                        p       { text-align: justify; }
                        li.main { padding-bottom: 3em; }
                </style>
        </head>

        <body>
                <h1><a href="../../..">Manuel Gómez</a> — <a href="../..">Cursos</a> — <a href="..">CI3825</a> — <a href=".">Enero–Marzo de 2012</a></h1>
                <hr/>
                <h2>2012‒02‒21 (semana 7): Otra consulta del primer proyecto</h2>
                <p>Acá les dejo mi respuesta a una consulta que recibí sobre el primer proyecto.  Espero que les sirva.</p>
                <hr/>
                <ol>
                        <li class="main">
                                <blockquote>
                                        <p>Sabes que en el proyecto de S.O. creemos que terminamos las dos versiones.  Para la versión de procesos obviamente es mas fácil verificar el resultado que para la parte de hilos (que parece estar haciéndolo bien), sin embargo me pregunto si estamos utilizando bien el mutex ya que nos costo un poco decidir en que parte lo usaríamos.</p>
                                </blockquote>
                                <p>Como regla general, todo acceso a una variable compartida entre hilos debe hacerse habiendo reservado a su <code>mutex</code> asociado.  Hay algunas excepciones muy particulares: si un hilo únicamente desea <em>leer</em> de una variable compartida y no está particularmente interesado en que esa lectura esté sincronizada con respecto a una posible escritura en proceso por parte de otro hilo, podría hacerse la lectura sin reservar el <code>mutex</code> asociado a la variable compartida.</p>
                                <p>De esto no interpreten que “si solo voy a leer de la variable, no hace falta reservar su <code>mutex</code>”.  En cambio, significa “si no me importa mucho que esto funcione de forma predecible Y solo voy a leer, entonces no hace falta reservar su <code>mutex</code>”.  Por ejemplo, si lo que están haciendo es imprimir un mensaje de diagnóstico con el valor de la variable compartida, y lo quieren hacer rápidamente como ayuda al desarrollo, podrían dejar de reservar el <code>mutex</code>.  Si están haciendo cualquier cuenta con el valor de la variable que involucre al control del flujo de ejecución de sus hilos, es <em>probable</em> que sea importante reservar el <code>mutex</code> aunque solo hagan una lectura.</p>
                                <p>El criterio exacto depende mucho de cada situación particular.  Lo que sí es universalmente cierto es que si van a <em>escribir</em> a una variable compartida, <em>deben</em> haber reservado su <code>mutex</code>.</p>
                        </li>
                        <li class="main">
                                <blockquote>
                                        <p>Tenemos un arreglo de un struc que contiene en cada casilla todos los datos que una atracción necesita para funcionar (capacidad, tiempo, cola) y un dato llamado sig que es donde el coloca luego de hacer sleep() el numero de personas que se "bajaron" de la atracción e irán a la cola siguiente.</p>
                                </blockquote>
                                <p>Correcto: esas personas van a la atracción siguiente únicamente después de bajarse de la actual.  Apenas termine el <code>sleep</code> de una atracción, debe comunicarse a la siguiente que el número de personas que se acaban de bajar ahora estará encolado en la que le sigue.</p>
                        </li>
                        <li class="main">
                                <blockquote>
                                        <p>Cada hilo lee antes de empezar del ".sig" que corresponde a la casilla del hilo anterior a el (en el caso del primero, este lee del ultimo hilo) y añade ese numero a su cola, luego procede a hacer sleep. Luego cada hilo debe "borrar lo que leyo" pero para "borrarlo" debe accesar al .sig del otro hilo, por lo que esa parte la hemos colocado dentro del mutex  así:</p>
                                </blockquote>
                                <p>Como el <code>.sig</code> se usa esencialmente como una variable de comunicación entre un productor y un consumidor, lo ideal sería que el consumidor “borre lo que leyó” inmediatamente después de leerlo.  En este caso no es inválido “borrarlo” después del <code>sleep</code> porque solo hay un consumidor asociado a ese productor; es decir que los visitantes salientes de una atracción siempre van a una única atracción particular que es fija para la atracción de la que salen.  En cualquier caso es preferible que la lectura a esa variable compartida suceda al mismo tiempo que la escritura, porque así se aseguran de que hay sincronización absoluta con una sola reserva del <code>mutex</code>, y el valor de la variable de comunicación tiene un significado uniforme fuera de una región crítica: siempre representa al número de personas que salieron de una atracción y aun no están en la atracción siguiente (ni en su cola, ni montados).</p>
                                <p>Como lo tienen ahorita, las cosas no son tan claras: es posible que ese número represente “esta cantidad de gente salió de una atracción a la siguiente, y todavía no han sido incorporados a la cola ni están montados”.  Pero también puede significar “esta cantidad de gente salió de una atracción, y algunos están en la cola de la siguiente, y otros ya están montados en la siguiente, y otros ya se bajaron y van a la que le sigue pero aun no se ha modificado el contador”.</p>
                                <p>Es preferible que el significado de una variable compartida no sea tan complejo, y la simplicidad se logra si hacen toda la manipulación de esa variable dentro de una sola sección crítica.  Ahorita están leyendo <em>fuera</em> de una sección crítica, y están “borrando” lo que leyeron <em>dentro</em> de una sección crítica pero en otra parte del código que se ejecuta en otro momento.  Esto <em>no</em> invalida su solución, pero hace que sea relativamente sencillo introducir cambios que sí la invaliden; mi recomendación de hacer ambas operaciones (lectura y actualización) en una sola sección crítica no es una cuestión de correctitud, sino de mantenibilidad.  Es importante que el diseño de un sistema concurrente haga todo lo posible por facilitar la deducción de información sobre su estado.</p>
                        </li>
                        <li class="main">
                                <blockquote>
<pre><code><![CDATA[
void *atracciones_Parque(void *arg) {
        double timehijo;
        int n, lee, pasa, montados; 
        int i = (int)(intptr_t)arg;
]]></code></pre>
                                </blockquote>
                                <p>Acá están usando un truco típicamente conocido como <em>doble coerción</em> para evitar una advertencia del compilador.  Ese es un truco que funciona perfectamente con el compilador GCC, y como es el que están usando, esto no está necesariamente mal.  Yo suelo recomendar fuertemente que usen extensiones del proyecto GNU a los lenguajes y otros estándares con los que estén trabajando, pero siempre bajo dos condiciones importantes:</p>
                                <ul>
                                        <li>Que la extensión esté bien diseñada y facilite significativamente la seguridad, la eficiencia y/o la mantenibilidad de sus programas.</li>
                                        <li>Que la extensión esté orientada a mejorar la portabilidad de sus programas.  Sí, suena contradictorio, pero muchas extensiones, a pesar de no constituir comportamiento estandarizado ni realmente portable, son el origen de nuevas adiciones a los estándares técnicos que definen las tecnologías que usan.  Estos casos típicamente son estándares <em>de facto</em>, y a veces hasta están incluidos en los borradores de las nuevas versiones de los estándares relevantes.</li>
                                </ul>
                                <p>En cualquier caso, hacer que su programa dependa de comportamiento que escape a los estándares es una decisión que deben tomar a nivel de cada proyecto usando sus criterios como programadores.  No es inválido hacerlo, pero es importante tener buenas razones para dejar de seguir un lineamiento establecido en un documento de estandarización.</p>
                                <p>Estos son dos párrafos relevantes del borrador más cercano que tengo (N1570, publicado el 2011‒04‒12) del documento final publicado como ISO/IEC 9899:2011, que es la versión del estándar internacional que define el lenguaje de programación C:</p>
                                <blockquote>
                                        <h3>6.3.2.3 <strong>Pointers</strong></h3>
                                        <ol>
                                                <li><strong>A pointer to void may be converted to or from a pointer to any object type. A pointer to any object type may be converted to a pointer to void and back again; the result shall compare equal to the original pointer.</strong></li>
                                                <li>For any qualifier <code>q</code>, a pointer to a non‐<code>q</code>‐qualified type may be converted to a pointer to the <code>q</code>‐qualified version of the type; the values stored in the original and converted pointers shall compare equal.</li>
                                                <li>An integer constant expression with the value 0, or such an expression cast to type <code>void *</code>, is called a null pointer constant.) If a null pointer constant is converted to a pointer type, the resulting pointer, called a null pointer, is guaranteed to compare unequal to a pointer to any object or function.</li>
                                                <li>Conversion of a null pointer to another pointer type yields a null pointer of that type.  Any two null pointers shall compare equal.</li>
                                                <li><strong>An integer may be converted to any pointer type. Except as previously specified, the result is implementation‐defined, might not be correctly aligned, might not point to an entity of the referenced type, and might be a trap representation.)</strong></li>
                                                <li><strong>Any pointer type may be converted to an integer type. Except as previously specified, the result is implementation‐defined. If the result cannot be represented in the integer type, the behavior is undefined. The result need not be in the range of values of any integer type.</strong></li>
                                                <li>A pointer to an object type may be converted to a pointer to a different object type.  If the resulting pointer is not correctly aligned) for the referenced type, the behavior is undefined.  Otherwise, when converted back again, the result shall compare equal to the original pointer.  When a pointer to an object is converted to a pointer to a character type, the result points to the lowest addressed byte of the object.  Successive increments of the result, up to the size of the object, yield pointers to the remaining bytes of the object.</li>
                                        </ol>
                                </blockquote>
                                <p>[El énfasis es mío.]</p>
                                <p>Además, POSIX.1‐2008 especifica esto en su descripción de <code>stdint.h</code>:</p>
                                <blockquote>
                                        <p>The following type designates a signed integer type with the property that any valid pointer to void can be converted to this type, then converted back to a pointer to void, and the result will compare equal to the original pointer: intptr_t</p>
                                </blockquote>
                                <p>Imagino que cuando escribieron la llamada a <code>pthread_create</code>, el último argumento (el <code>void *</code> que se le pasa a la función del hilo como parámetro) dice algo como <code>(void *)i</code>, donde <code>i</code> es el contador del ciclo en el que crean todos los hilos.  En efecto, es necesario pasar al hilo algo que le diga cuál es su índice para que pueda hacer el resto del trabajo.  El problema es que el lenguaje de programación C no provee ninguna garantía de que un valor entero pueda convertirse en un valor de tipo <code>void *</code> y luego volver a convertirse en un entero, y que el valor resultante sea igual al valor original.</p>
                                <p>Cuando escriben el código <code>(void *)i</code> en la llamada a <code>pthread_create</code>, eso es una conversión de <code>int</code> a <code>void *</code>.  Sea cual fuere el valor de <code>i</code>, esa conversión es normada por ISO/IEC §6.3.2.3.5, que especifica que el valor resultante es <strong>definido por la implementación</strong>.  En particular, nada asegura que el valor de <code>i</code> pueda luego ser recuperado a partir del valor resultante de esa conversión, que podría ser cualquier cosa.</p>
                                <p>Cuando escriben el código <code>(int)(intptr_t)arg</code> en la inicialización de <code>i</code>, están haciendo dos conversiones: la primera convierte a <code>arg</code>, un <code>void *</code>, en un <code>intptr_t</code> (que es un <em>tipo entero</em>), y la segunda convierte a ese nuevo valor de tipo <code>intptr_t</code> en un valor final de tipo <code>int</code>.  La primera conversión es, entonces, del tipo referido en ISO/IEC 9899:2011 §6.3.2.3.6: se está convirtiendo un valor de tipo apuntador a <code>void</code> en un valor de un tipo entero.  §6.3.2.3.6 dice que el valor resultante es <strong>definido por la implementación</strong>, y además que si el valor no puede representarse en ese tipo entero, <strong>el comportamiento es indefinido</strong>.</p>
                                <p>Esto último no sucede, y POSIX lo asegura: <code>intptr_t</code> es un tipo entero con signo con la propiedad de que cualquier apuntador a <code>void</code> puede convertirse en él (y si se vuelve a convertir en un apuntador a <code>void</code>, se tiene el apuntador original).  Noten que este ciclo de conversiones es el contrario al que les interesaría: la idea es convertir un entero a <code>void *</code> y luego recuperar el entero; no nos interesa convertir un <code>void *</code> a entero y recuperar el <code>void *</code>.  En cualquier caso, el valor de tipo <code>intptr_t</code> que resulta sigue siendo algo definido por la implementación sin ninguna garantía de que sea el mismo entero que habíamos pasado originalmente.</p>
                                <p>Si esto funciona para ustedes, es porque la versión de GCC que están usando hace lo que ustedes quieren que haga en la plataforma en la que lo están usando.  Si tienen un documento de GCC, o de algún estándar que GCC obedezca, que especifique ese comportamiento, todo está bien dentro de la isla de no‐portabilidad en la que han decidido vivir.  Si quieren programar de manera portable y consistente, deberían hacer algo totalmente diferente.</p>
                                <p>El mecanismo que les mostré en clase (y que está en la documentación) para pasar datos a un hilo consiste en tener un espacio de memoria que contenga los datos que se quieren pasar, y se le pasa al hilo <em>un apuntador a esos datos</em> convertido en un apuntador a <code>void</code>.  La segunda oración de ISO/IEC 9899:2011 §6.3.2.3.1 dice que un apuntador a cualquier tipo de objeto puede convertirse en un apuntador a <code>void</code>, y si el resultado de eso se convierte en un apuntador del tipo de objeto original, entonces el apuntador resultante es igual al original.  Es decir que la especificación del lenguaje de programación C sí asegura que la conversión cíclica que quieren hacer es válida <strong>si el tipo original y final es un tipo de apuntador</strong> (pero no lo especifica si es un tipo entero, que es lo que están usando ustedes.)</p>
                                <p>Lo que deberían estar haciendo es tener en alguna parte estable de la memoria una copia para cada iteración del valor del contador del ciclo, todas por separado e inmutables, y a cada hilo le pasan el apuntador a la copia correspondiente del valor del contador en cada iteración.  Es tan sencillo como declarar, antes del inicio del ciclo, <code>int is[n];</code> (donde <code>n</code> es el número de atracciones) y al entrar a cada iteración asignar el valor del contador al elemento correspondiente del arreglo: <code>is[i] = i;</code>.  Luego, cuando llaman a <code>pthread_create</code>, en su último parámetro deberían pasar <code>&amp;is[i]</code>, y la inicialización de <code>i</code> en el inicio de la función de los hilos debería ser con la expresión <code>*((int *)arg)</code>; es decir, convierte el apuntador a <code>void</code> pasado al hilo en un apuntador a <code>int</code>, que es lo que era originalmente, y retorna el entero apuntado por él.</p>
                                <p>Este es un problema sutil que en principio podría no tener absolutamente ningún efecto, pero podría hacer que su programa falle misteriosamente si actualizan su compilador, o si cambian a una máquina de 64 bits, o si corren su programa con muchos hilos.  Para este proyecto particular es una tontería de error, pero si llegan a implementar un sistema concurrente de escala considerable y que deba ser portable (como el navegador Web en el que seguramente están leyendo esto, o cualquiera de los cientos de paquetes sobre los que ese navegador está corriendo, como el sistema gráfico, las bibliotecas de redes, etc), este tipo de detalle se vuelve sumamente importante.</p>
                        </li>
                        <li class="main">
                                <blockquote>
<pre><code><![CDATA[
        struct timeval ti, th;
        n = 0;
        lee = 0;
        gettimeofday(&ti, NULL);   // Instante inicial
]]></code></pre>
                                </blockquote>
                                <p>La página del manual del programador de Linux referente a <code>gettimeofday</code>, incluida en la versión 3.37 del proyecto de páginas de manual de Linux, dice</p>
                                <blockquote>
                                        <h3>CONFORMING TO</h3>
                                        <p>SVr4, 4.3BSD.  POSIX.1‐2001 describes gettimeofday() but not settimeofday().  POSIX.1‐2008 marks gettimeofday() as obsolete, recommending the use of clock_gettime(2) instead.</p>
                                </blockquote>
                                <p><code>clock_gettime</code> tiene un funcionamiento muy similar a <code>gettimeofday</code>, pero es la llamada recomendada por POSIX.  El uso que les interesa es algo como esto:</p>
                                <blockquote>
<pre><code><![CDATA[
#include <stdio.h>    // para “perror”
#include <stdlib.h>   // para “exit”
#include <sysexits.h> // para “EX_SOFTWARE”
#include <time.h>     // para “clock_gettime” y “CLOCK_REALTIME”

/* … */

struct timespec ti, th;

/* … */

if (clock_gettime(CLOCK_REALTIME, &ti) != 0) {
    perror("clock_gettime");
    exit(EX_SOFTWARE);
}
]]></code></pre>
                                </blockquote>
                                <p>La estructura <code>timespec</code> es muy similar a la estructura <code>timeval</code> usada por <code>gettimeofday</code>:</p>
                                <blockquote>
<pre><code><![CDATA[
struct timespec {
    time_t   tv_sec;        /* seconds */
    long     tv_nsec;       /* nanoseconds */
};
]]></code></pre>
                                </blockquote>
                                <p>La única diferencia es que el segundo campo es de tipo <code>long</code> en vez de <code>suseconds_t</code>, se llama <code>tv_nsec</code> en vez de <code>tv_usec</code>, y representa nanosegundos en vez de microsegundos.</p>
                        </li>
                        <li class="main">
                                <blockquote>
<pre><code><![CDATA[
        while (iteraciones - n > 0) {
]]></code></pre>
                                </blockquote>
                                <p>Sobre lo que deben hacer los hilos trabajadores, el enunciado dice explícitamente así:</p>
                                <blockquote>
                                        <p>Los procesos/hilos trabajadores deben:</p>
                                        <ol>
                                                <li><p>actualizar la cola de espera. La primera vez sólo actualizarán con la información del archivo y en lo sucesivo actualizarán la cola con los usuarios que llegan de la atracción anterior en el anillo,</p></li>
                                                <li><p>en cada iteración, montar tantos usuarios como la capacidad del aparato permita y de acuerdo a cuántos están en la cola (esta simulación es simplemente una resta al número de personas en la cola, la cola puede estar vacía),</p></li>
                                                <li><p>dormir durante el "tiempo de uso de la atracción",</p></li>
                                                <li><p>al despertar, enviar los usuarios que estaban montados a la siguiente atracción en el anillo, leer cuántos usuarios llegan de la atracción anterior y encolarlos en su cola,</p></li>
                                                <li><p>los pasos 2 a 4 se repiten durante el "nro. de iteraciones". Al finalizar todas las iteraciones, la atracción se cierra, e informa al proceso/hilo principal cuántos usuarios quedaron en la cola,</p></li>
                                                <li><p>además, al finalizar, cada trabajador debe imprimir el tiempo consumido durante su simulación.</p></li>
                                        </ol>
                                </blockquote>
                        </li>
                        <li class="main">
                                <blockquote>
<pre><code><![CDATA[
                if (n == 0) {      
                        montados = d[i].numCola - d[i].capacidad;
                        d[i].numCola = montados;
                }
                else {  
                        if (i == 0) {
                                lee = d[atracciones-1].sig;
                                d[i].numCola = d[i].numCola + lee;
                                montados = d[i].numCola - d[i].capacidad;
                                d[i].numCola = montados;
                        } else {
                                lee = d[i-1].sig;
                                d[i].numCola = d[i].numCola + lee;
                                montados = d[i].numCola - d[i].capacidad;
                                d[i].numCola = montados;
                        }
                }
]]></code></pre>
                                </blockquote>
                                <p>Primero, un comentario de estilo: todo ese código se puede resumir así:</p>
                                <blockquote>
<pre><code><![CDATA[
                if (n != 0) d[i].numCola += d[(i == 0 ? atracciones : i) - 1].sig;
                montados = (d[i].numCola -= d[i].capacidad);
]]></code></pre>
                                </blockquote>
                                <p>La mantenibilidad de dos líneas sin repetición seguramente es preferible a la de 17 con un montón de copias de lo mismo.  Si les toca hacer un cambio en esta parte del código, será de lejos preferible tener menos código que alterar.</p>
                                <p>Esta parte debería implementar los puntos 1 y 2 de la lista del enunciado que copié antes.</p>
                                <p>Acá aparece el problema que les mencioné antes: están accediendo a la variable compartida <code>sig</code> fuera de un <code>mutex</code>; la información que extraen de ella se usará para actualizar variables privadas (no compartidas) y también a la propia variable compartida (al “borrar” a los visitantes entrantes), pero esa última actualización no la están haciendo en este punto, por lo que la semántica de su variable compartida se complica.  Esto no es incorrecto en el caso particular de su código, pero sí es indeseable y peligroso para ustedes mismos: se vuelve más complejo razonar sobre el estado de su programa, que ya es suficientemente complejo por el solo hecho de involucrar concurrencia.  Lo ideal sería que acá mismo reservaran el mutex asociado a esta variable compartida particular, sumaran su valor a la cola del hilo actual, y la dejaran en cero.  Todo esto solo aplica, claro, para iteraciones que no sean la primera.</p>
                                <p>Un comentario de estilo: es importante que haya una correspondencia entre los símbolos usados para representar objetos de su programa y el significado de esos objetos.  <code>montados</code> debería representar el número de visitantes montados en la atracción en una iteración.  Si no representa eso, debería tener algún otro nombre.  La selección de símbolos (y los comentarios, y el espacio en blanco y demás) es arbitraria y es irrelevante para la semántica del programa, pero es importante para que el proceso de programación se les haga fácil a ustedes, que son los programadores.</p>
                                <p>En este caso particular, probablemente hubiera sido mejor asignar a <code>montados</code> el mínimo entre la capacidad de la atracción y la cantidad de visitantes en la cola, y luego restar ese número a la cola:</p>
                                <blockquote>
<pre><code><![CDATA[
#define MIN(a, b) ((a) <= (b) ? (a) : (b))

montados = MIN(d[i].capacidad, d[i].numCola);
d[i].numCola -= montados;
]]></code></pre>
                                </blockquote>
                                <p>En efecto esto implementaría lo especificado en el segundo punto de la lista del enunciado.</p>
                        </li>
                        <li class="main">
                                <blockquote>
<pre><code><![CDATA[
                sleep(d[i].tiempo);             
]]></code></pre>
                                </blockquote>
                                <p>Esto en efecto implementa el tercer punto de la lista del enunciado que copié antes.</p>
                        </li>
                        <li class="main">
                                <blockquote>
<pre><code><![CDATA[
                /* La cola tenia menos personas que capacidad*/
                if (d[i].numCola < 0) {      
                        pasa = d[i].capacidad - abs(d[i].numCola);
                        d[i].numCola = 0;
                }
]]></code></pre>
                                </blockquote>
                                <p>Acá están compensando la deficiencia semántica del valor que decidieron almacenar antes: en realidad la cola nunca llega a tener una cantidad negativa de visitantes en espera (¿qué significaría “hay −3 personas en la cola”?), pero como le asignaron un número extraño a una variable que debería haber representado “la cantidad de visitantes en espera en la cola de la atracción <code>i</code>”, ahora tienen que aplicar una corrección también extraña.</p>
                                <p>Por cierto, el código <code><![CDATA[if (x < 0) { f(abs(x)); }]]></code> es exactamente equivalente a <code><![CDATA[if (x < 0) { f(-x); }]]></code>.</p>
                        </li>
                        <li class="main">
                                <blockquote>
<pre><code><![CDATA[
                /* Seccion critica */
                pthread_mutex_lock( &mutex1 );
]]></code></pre>
                                </blockquote>
                                <p>Acá veo que están usando un mismo <code>mutex</code> para todas las variables compartidas entre todos los hilos.  Deberían usar un <code>mutex</code> diferente para la comunicación entre cada par de hilos adyacentes.  Si usan un solo <code>mutex</code>, están restringiendo la ejecución concurrente de su programa mucho más de lo necesario, y eso desaprovecha la capacidad de concurrencia disponible en el sistema y le quita el sentido a escribir un programa concurrente; en efecto, usar un solo <code>mutex</code> hace que su programa resulte muy similar a un programa <em>serial</em>.</p>
                        </li>
                        <li class="main">
                                <blockquote>
<pre><code><![CDATA[
                if (i == 0) {
                        d[atracciones-1].sig = d[atracciones-1].sig - lee;              
                } else {
                        d[i-1].sig = d[i-1].sig - lee;
                }       
                if (montados < 0) {                    
                        d[i].sig = d[i].sig + pasa;
                } else {
                        d[i].sig = d[i].sig + d[i].capacidad;   
                }
]]></code></pre>
                                </blockquote>
                                <p>Acá también hay reducciones considerables que se pueden aplicar:</p>
                                <blockquote>
<pre><code><![CDATA[
                d[(i == 0 ? atracciones ? i)-1].sig -= lee;
                d[i].sig += (montados < 0 ? pasa : d[i].capacidad);
]]></code></pre>
                                </blockquote>
                                <p>La primera de esas líneas hace lo que les sugiero que hagan no acá sino antes de hacer <code>sleep</code>; si la hacen antes, evidentemente deben hacerla en una sección crítica; es decir, habiendo reservado el <code>mutex</code> correspondiente a esa variable compartida.</p>
                                <p>La segunda de esas líneas, si se fijan bien, utiliza <em>otra</em> variable compartida.  También debe ir en una sección crítica, pero el <code>mutex</code> sería el correspondiente a <code>d[i].sig</code>, que no es el mismo <code>mutex</code> referido en el párrafo anterior.</p>
                                <p>Estas dos cosas en efecto implementan lo requerido en el cuarto elemento de la lista del enunciado.</p>
                        </li>
                        <li class="main">
                                <blockquote>
<pre><code><![CDATA[
                pthread_mutex_unlock( &mutex1 );
                n = n+1;                
        } 
        /* Se imprime lo correspondiente por cada Trabajador */
        gettimeofday(&th, NULL);   // Instante final
        timehijo = (th.tv_sec - ti.tv_sec)*1000 + (th.tv_usec - ti.tv_usec)/1000.0;
        printf("Trabajador \t %d \t %g ms\n",(int)pthread_self(), timehijo);
        pthread_exit((void*)pthread_self());
}
]]></code></pre>
                                </blockquote>
                                <p>Al pasar el resultado de <code>pthread_self()</code> a la llamada a <code>pthread_exit</code>, están retornando el identificador del hilo a quien haya llamado a <code>pthread_join</code> con el identificador de ese hilo… pero si llamaron a <code>pthread_join</code> con ese identificador, ya tenían ese identificador, así que ¿qué sentido tiene pasarlo como valor de retorno?</p>
                                <p>Acá también tienen una llamada a <code>gettimeofday</code> que sería preferible sustituir por <code>clock_gettime</code>.</p>
                        </li>
                        <li class="main">
                                <blockquote>
                                        <p>De esa forma creemos que se evita que dos hilos toquen el .sig de la misma casilla del arreglo. Le reste el "lee" porque ya es posible que el hilo anterior haya escrito nuevamente y así solo se resta lo que ya se monto en la cola.</p>
                                        <p>Sin importar si eso esta bien o mal, que espero que este bien :) quería confirmar que cuando corres la versión de hilos, si colocas que todas las atracciones duren la misma cantidad de tiempo, el resultado varia dependiendo del orden en que se ejecuten los hilos (ya que ellos no se esperan).</p>
                                </blockquote>
                                <p>Lo ideal sería que el problema se hubiera especificado de una forma que hiciera que las corridas fueran independientes de cualquier condición de carrera.  Esto hubiera sido posible si estuviera especificado (o fuera compatible con la especificación y con las aclaratorias de Yudith) que los hilos deban esperar a que haya suficientes visitantes en sus colas para llenar la capacidad de sus atracciones antes de comenzar una de sus iteraciones.</p>
                                <p>El problema con esta idea es que no es trivial definir el problema de esta manera y evitar que ocurra interbloqueo, y además sería bastante más complejo programar la interacción entre los hilos: habría que programar un verdadero sistema de productores y consumidores con variables de condición para hacerlo bien.  Sería un problema adecuado para la cadena de operativos, donde se resuelven estas cosas con herramientas más avanzadas que evitan todos los problemas de intentar hacer esto con hilos y memoria compartida.</p>
                                <p>Es importante entender que la intención de la programación concurrente es que esto <strong>no</strong> ocurra: un programa concurrente bien diseñado <strong>no</strong> debería estar sujeto a condiciones de carrera.  Sin embargo, el alcance de los objetivos de este proyecto debe limitarse para que sea viable para un curso de este nivel, y uno de los sacrificios necesarios es el buen diseño de la concurrencia.  En general, cuando en su ejercicio profesional en computación requiera que diseñen o implementen un sistema concurrente, su preocupación es absolutamente válida: el resultado <strong>no</strong> debería variar dependiendo del orden en que se ejecuten los hilos <strong>a menos que</strong> ese orden sea controlado mediante primitivas de sincronización, o que ese orden no afecte el resultado del cómputo del sistema.</p>
                        </li>
                </ol>
                <hr/>
                <p>
                        <a href="http://validator.w3.org/check?uri=referer">
                                <img src="http://www.w3.org/Icons/valid-xhtml11" alt="Valid XHTML 1.1" height="31" width="88"/>
                        </a>
                </p>
                <p>Y lo escribí a mano en Vim sin más referencia que las primeras 4 líneas del boilerplate.</p>
                <p>Like a boss.</p>
        </body>
</html>
