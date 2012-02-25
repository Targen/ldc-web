<?php
        // Content negotiation? #merwebo.
        // XHTML ftw; una solución decente requeriría una configuración de Apache decente.
        header('Content-Type:application/xhtml+xml;charset=utf-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
        <head>
                <meta http-equiv="Content-type" content="application/xhtml+xml;charset=utf-8"/>
                <title>Notas de Manuel Gómez sobre CI3825 — Más consultas del primer proyecto</title>
                <style type="text/css">p { text-align:justify; }</style>
        </head>

        <body>
                <h1><a href="../../../..">Manuel Gómez</a> — <a href="../../..">Cursos</a> — <a href="../..">CI3825</a> — <a href="..">Enero–Marzo de 2012</a></h1>
                <hr/>
                <h2>2012‒02‒24 (semana 7): Más consultas del primer proyecto</h2>
                <p>Acá les dejo las preguntas y respuestas de varias consultas que me han hecho estudiantes del curso sobre el primer proyecto.  Espero que les sirvan.</p>
                <ol>
                        <li><a href="#p1" >Reciclaje de <em>pipe</em>s                                                  </a></li>
                        <li><a href="#p2" >Espera por la terminación de los trabajadores e impresión de sus resultados  </a></li>
                        <li><a href="#p3" >Cierre de <em>file descriptors</em> de <em>pipe</em>s                        </a></li>
                        <li><a href="#p4" >Unidades de tiempo y <code>gettimeofday</code> vs. <code>clock_gettime</code></a></li>
                        <li><a href="#p5" >Pasaje de valores de retorno desde un hilo                                   </a></li>
                        <li><a href="#p6" ><code>pthread_join</code> infinito                                           </a></li>
                        <li><a href="#p7" >“vector” de enteros                                                          </a></li>
                        <li><a href="#p8" >Problemas de referencias                                                     </a></li>
                        <li><a href="#p9" >Formas alternativas de desreferencia no‐trivial                              </a></li>
                        <li><a href="#p10">Pasaje de parámetros por referencia reutilizada                              </a></li>
                        <li><a href="#p11">Sobreescritura sin sincronización                                            </a></li>
                </ol>
                <ol>
                        <li id="p1">
                                <h3>Reciclaje de <em>pipe</em>s</h3>
                                <h4>Pregunta</h4>
                                <blockquote>
                                        <p>-En el caso del proyecto, basta con tener UN pipe para comunicarse con el padre verdad?. Inicialmente crei que se necesitaba uno por cada uno, pero me estaba dando muchos errores, y al tener uno solo me parece que funciona. Pero no se con seguridad si es asi y cual es el motivo por el que basta uno?</p>
                                </blockquote>
                                <h4>Respuesta</h4>
                                <p>Cada trabajador debe tener abiertos dos <em>pipe</em>s a los que podrá escribir: uno para reportar al padre cuántos visitantes quedaron en su cola cuando finalice sus iteraciones, y otro para escribir al trabajador que maneja la siguiente atracción la cantidad de visitantes que salen de cada una de sus iteraciones. (Esos otros procesos evidentemente deben tener abierto el otro lado del <em>pipe</em> para leer esos datos, excepto en el caso de trabajadores que ya hayan terminado).</p>
                                <p>En el caso de los <em>pipe</em>s usados para la comunicación entre trabajadores, no es viable ninguna solución que utilice menos que un <em>pipe</em> por trabajador: si hubiera menos que esa cantidad de <em>pipe</em>s, sería posible que un trabajador lea datos que <em>no</em> son para él. Si la longitud de los datos escritos por cada trabajador llegara a ser mayor que <code>PIPE_BUF</code> bytes (que no es menor que 512 según POSIX.1-2001), podría suceder, además, que los mensajes de varios trabajadores estén solapados. Como son mensajes cortos, podría asegurarse que esto no pase sin ningún problema (y en ese caso, POSIX asegura que la escritura se haga atómicamente), pero esto no elimina el problema de que los trabajadores lean (y consuman) mensajes que <em>no</em> estaban destinados a ellos. En esos casos se podría volver a escribir el mensaje al <em>pipe</em> compartido para que esté disponible al trabajador al que correspondía (si en el mensaje se incluye su identificación), pero dependiendo de otros detalles de la implementación, el algoritmo resultante podría causar inanición: el mensaje podría no llegar nunca a su destino.</p>
                                <p>En el caso de los <em>pipe</em>s usados para la comunicación con el padre al finalizar, sí es viable que se utilice menos que un <em>pipe</em> por trabajador. La escritura de los mensajes sería atómica porque los mensajes producidos al finalizar son cortos: incluyen simplemente un entero en forma binaria que mide exactamente 4 bytes (u 8, en una máquina de 64 bits) que es menos que 512, y si lo escriben en forma de texto, tendría los dígitos decimales correspondientes al entero seguidos de un delimitador (un byte que no sea un dígito), que también sería menos que 512 bytes (en el caso de 32 bits, el mayor entero sin signo es 4294967295, y en el caso de 64 bits es 18446744073709551615, que tienen 10 y 20 dígitos decimales, respectivamente; al añadir el delimitador, 11 y 21 son, claro, aun menores que 512). Esto asegura que los mensajes de los trabajadores no se mezclen en el <em>pipe</em> compartido, y como solo el padre leerá, el problema del párrafo anterior no existe.</p>
                        </li>
                        <li id="p2">
                                <h3>Espera por la terminación de los trabajadores e impresión de sus resultados</h3>
                                <h4>Pregunta</h4>
                                <blockquote>
                                        <p>-Si el padre hace un wait para esperar a que los hijos terminen, espera hasta que terminen todos sus hijos verdad? y luego es que poodria leer la informacion e imprimir lo que corresponde?. Es que me confunde que el enunciado dice "a medida que los trabajadores terminen, imprimir el numero de personas que quedaron en la cola ...", en realidad imprimira, despues de que todos terminen cuando salga del wait... o estoy equivocada??</p>
                                </blockquote>
                                <h4>Respuesta</h4>
                                <p>POSIX.1‐2008, en su especificación de las funciones <code>wait</code> y <code>waitpid</code>, dice</p>
                                <blockquote>
                                        <p>The <code>wait()</code> and <code>waitpid()</code> functions shall obtain status information pertaining to one of the caller's child processes. Various options permit status information to be obtained for child processes that have terminated or stopped. If status information is available for two or more child processes, the order in which their status is reported is unspecified.</p>
                                </blockquote>
                                <p>Las funciones <code>wait</code> y <code>waitpid</code> retornan cuando <em>un</em> hijo cambia de estado (aunque también pueden retornar si son interrumpidas por una señal). Si quieren esperar por la terminación de <em>todos</em> los hijos, deben llamar repetidamente a <code>wait</code> hasta que todos los hijos indiquen que terminaron. Si usan <code>waitpid</code>, asegúrense de que el cambio de estado fue en efecto una terminación (porque dependiendo de las opciones que le especifiquen, <code>waitpid</code> puede retornar el identificador de un proceso que se haya hecho detener o continuar con señales). Recuerden también que <code>wait</code> y <code>waitpid</code> pueden retornar si son interrumpidos por señales, en cuyo caso sus códigos de retorno indicarían errores (que no deben causar que el programa aborte, sino que se vuelva a ejecutar la llamada).</p>
                                <p>Cuando <code>wait</code> retorna indicando que un hijo terminó, pueden imprimir inmediatamente la información indicada en el <em>pipe</em> antes de continuar el ciclo de <code>wait</code> para esperar por el retorno del siguiente trabajador (en caso de que falten). Esto es lo que indica esa parte del enunciado.</p>
                        </li>
                        <li id="p3">
                                <h3>Cierre de <em>file descriptors</em> de <em>pipe</em>s</h3>
                                <h4>Pregunta</h4>
                                <blockquote>
                                        <p>-Se que el padre debe cerrar el descriptor que no vaya a utilizar, en este caso el de escritura. Pero tambien deberia cerrar los descriptores de los pipes del anilllo porque el no los usa?</p>
                                </blockquote>
                                <h4>Respuesta</h4>
                                <p>Sí, pero solo cuando haya terminado de crear a todos sus hijos. Si los cierran antes, los hijos serán creados con todos los <em>pipe</em>s cerrados, y no podrán usarlos. Si no los cierran, se les dificultará detectar en los trabajadores que el anterior ya terminó y no enviará más visitantes, y que el siguiente terminó y no recibirá más visitantes.</p>
                        </li>
                        <li id="p4">
                                <h3>Unidades de tiempo y <code>gettimeofday</code> vs. <code>clock_gettime</code></h3>
                                <h4>Pregunta</h4>
                                <blockquote>
                                        <p>- Y finalmente los resultados de los tiempos deben estar expresados en microsegundos?..es que vi que pusiste que usaramos clock_gettime, yo ya lo habia hecho con gettimeofday. ¿Lo puedo dejar con gettimeofday o lo cambio a clock_gettime y transformo a ms??</p>
                                </blockquote>
                                <h4>Respuesta</h4>
                                <p>La profesora Yudith envió una comunicación hace varias semanas indicando una modificación al enunciado: los tiempos serán en segundos, en vez de en microsegundos que es lo que se intuía del enunciado (aunque no estaba especificado). Copio acá su contenido:</p>
                                <blockquote>
                                        <h5>Una aclaratoria para el Proyecto</h5>
                                        <p>de Yudith C. Cardinale V. - martes, 7 de febrero de 2012, 18:46</p>
                                        <p>Estimados estudiantes</p>
                                        <p>Para facilitarles el manejo de los tiempos, consideren que el parámetro que corresponde al tiempo de simulación de cada atracción, estará dado en segundos y no en ms como se intuye en el ejemplo de prueba del enunciado.</p>
                                        <p>Así cada trabajador dormirá por el tiempo indicado, si usan la llamada al sistema sleep:</p>
                                        <blockquote>
<pre><code><![CDATA[
#include <unistd.h>
unsigned int sleep(unsigned int seconds);
]]></code></pre>
                                        </blockquote>
                                        <p>Saludos</p>
                                        <p>-yudith</p>
                                </blockquote>
                                <p>No es inválido que usen <code>gettimeofday</code>; mi sugerencia es para aumentar la calidad y portabilidad de su proyecto, pero no es un requerimiento del enunciado que su programa se ajuste a las recomendaciones de las últimas versiones de POSIX. El requerimiento, si mal no recuerdo, es que funcionen en el LDC. En cualquier caso, POSIX.1‐2008 especifica a <code>gettimeofday</code>, pero indica que las aplicaciones <em>no deberían</em> usarla, no que <em>no deben</em> usarla.</p>
                                <p>La distinción es sutil: POSIX, al igual que muchísimos otros documentos de estandarización (como RFCs, especificaciones de lenguajes de programación, de protocolos de comunicación, de formatos de archivos, etc) utilizan los términos “should” y “should not”, entre otros, para especificar <em>recomendaciones normativas</em> cuyo cumplimiento no es un requerimiento para ser conforme a la especificación, mientras que usan los términos “must”, “shall”, “must not” y “shall not”, entre otros, para especificar <em>requerimientos normativos</em>, que sí constituyen requerimientos para ser conforme a la especificación. Hay un documento que especifica una de las formas comunes de estas convenciones de documentación y muchos otros se basan en él: <a href="https://www.ietf.org/rfc/rfc2119">RFC 2119</a>. POSIX no hace referencia a este documento, pero en su sección <a href="http://pubs.opengroup.org/onlinepubs/9699919799/xrat/V4_xbd_chap01.html#tag_21_01_05">A.1.5</a> define reglas muy similares.</p>
                                <p>En cualquier caso, el uso de <code>gettimeofday</code> es muy similar al de <code>clock_gettime</code> (y eso no es sorprendente: <code>clock_gettime</code> fue diseñada específicamente para sustituir a <code>gettimeofday</code>, que tenía deficiencias en su diseño relacionadas con el manejo de zonas horarias). Lo importante de este asunto es que manejen el tiempo de cada atracción como una cantidad de <strong>segundos</strong>.</p>
                        </li>
                        <li id="p5">
                                <h3>Pasaje de valores de retorno desde un hilo</h3>
                                <h4>Pregunta</h4>
                                <blockquote>
                                        <p>Para el proyecto yo utilizo lo siguiente para esperar los hilos que creo:</p>
                                        <blockquote>
<pre><code><![CDATA[
pthread_join(trabajadores[i],(void **)&(tiempoCorrida))
]]></code></pre>
                                        </blockquote>
                                        <p>donde: <code>trabajadores</code> es un arreglo --&gt; <code><![CDATA[pthread_t trabajadores[numAtrac];]]></code> y <code><![CDATA[tiempoCorrida]]></code> un apuntador a entero --&gt; <code><![CDATA[int *tiempoCorrida;]]></code></p>
                                </blockquote>
                                <h4>Respuesta</h4>
                                <p>Para no caer en comportamiento indefinido, asegúrense de que ese apuntador apunte a un entero en la memoria cuyo tiempo de vida no termine antes de que ocurra el <code>pthread_join</code>; es decir que deberían haber hecho algo como <code><![CDATA[tiempoCorrida = malloc(sizeof(int));]]></code> o <code><![CDATA[tiempoCorrida = calloc(1, sizeof(int));]]></code> dentro del hilo (y no olviden hacer el correspondiente <code>free</code> en el hilo principal cuando ya hayan usado ese valor), o quizás algo como <code><![CDATA[tiempoCorrida = algo;]]></code> donde <code>algo</code> es la dirección de un objeto local de otra función o un objeto global, y que ese objeto siga existiendo cuando ejecutan <code>pthread_join</code>.</p>
                        </li>
                        <li id="p6">
                                <h3><code>pthread_join</code> infinito</h3>
                                <h4>Pregunta</h4>
                                <blockquote>
                                        <p>Pero sucede que cuando se ejecuta esa instruccion (<code><![CDATA[pthread_join(trabajadores[i],(void **)&(tiempoCorrida))]]></code>) se guinda el programa y no termina</p>
                                        <p>¿A qué se debe esto? ¿Que puedo estar haciendo mal?</p>
                                        <p>Cuando coloco esta instruccion en comentarios <code>//</code>, el main termina.</p>
                                        <p>En la funcion que mando a ejecutar con <code><![CDATA[pthread_create(&trabajadores[i], NULL, (void *)iterar, (void*)args )]]></code> (<code>iterar</code>) hago un <code><![CDATA[pthread_exit(tiempoCorrida);]]></code>, donde igualmente <code>tiempoCorrida</code> es un apuntador a entero.</p>
                                </blockquote>
                                <h4>Respuesta</h4>
                                <p>En principio, <code>pthread_join</code> solo retorna cuando ese hijo sale, ya sea porque su función principal retornó, o porque en alguna parte de su ejecución llamó a <code>pthread_exit</code>. Si no tienen comportamiento indefinido y <code>pthread_join</code> no retorna, es porque el hilo con el que pretenden sincronizarse sencillamente no está saliendo.</p>
                                <p>Sospecho que el ciclo en el que hacen <code>sleep</code> debe estar iterando infinitamente, o quizás le están pasando un argumento a <code>sleep</code> que es más grande de lo que ustedes creen y debería ser; ¿están manejando los tiempos indicados en la entrada en segundos como indicó Yudith hace unas semanas por e‐mail? ¿Están pasando en la entrada tiempos como 1000 y 10000, como los que estaban en los ejemplos del enunciado, e interpretándolos como segundos? En ese último caso, deberían cambiar esas entrada a 1 y 10.</p>
                                <p>Podrían usar <code>gdb</code> para poner un <em>breakpoint</em> en el punto de su programa donde deberían llamar a <code>pthread_exit</code>, o simplemente imprimir algo ahí para verificar si se llama o no. Lo más probable es que nunca se esté ejecutando.</p>
                        </li>
                        <li id="p7">
                                <h3>“vector” de enteros</h3>
                                <h4>Pregunta</h4>
                                <blockquote>
                                        <p>Ahora tengo otro problema: Yo deseo pasar por parametros a la funcion que llama a ejecutar el <code>pthread_create</code> 3 datos, para ello declaro un vector de enteros de tamaño 3 --&gt; <code>int args[3];</code></p>
                                </blockquote>
                                <h4>Respuesta</h4>
                                <p>Bien, pero hay dos detalles: uno tonto y uno importante.</p>
                                <p>El tonto es que eso no es un vector sino un arreglo. Cuando trabajen con C++ (en alguna otra materia, claro) seguramente trabajarán con una plantilla de clases definida en la biblioteca estándar de C++ (ISO/IEC 14882:2011 §23.3.6 [vector]) llamada <code>vector</code> que esencialmente produce arreglos que ajustan su capacidad automáticamente cuando les es necesario. Por otra parte, desde el punto de vista matemático, un vector es un elemento de un álgebra con ciertas propiedades; los arreglos de C no tienen esas propiedades. Eso es un arreglo, no un vector.</p>
                                <p>El detalle importante es que eso es exactamente <em>un</em> arreglo. Cuando escriben esa declaración, el compilador de C sabrá que debe reservar en la memoria espacio para exactamente tres enteros. Si pretenden que haya tres enteros <em>por hilo</em> en vez de tres enteros <em>en total</em>, tendrán que hacer algo ligeramente diferente.</p>
                        </li>
                        <li id="p8">
                                <h3>Problemas de referencias</h3>
                                <h4>Pregunta</h4>
                                <blockquote>
                                        <p>y al crear el hilo utilizo: <code><![CDATA[pthread_create(&trabajadores[i], NULL, iterar, (void *)args )]]></code>.</p>
                                </blockquote>
                                <h4>Respuesta</h4>
                                <p>Eso en principio no está mal siempre que <code>args</code> sea un apuntador a un objeto de cualquier tipo cuyo tiempo de vida sea al menos hasta que todos los hilos que tengan un apuntador a él hayan terminado de usarlo, y que si varios hilos lo usan, se asegure la exclusión mutua cuando hace falta.</p>
                                <p>Si los apuntadores que pasan a los hilos apuntan a objetos reservados en el <em>heap</em> (o a un lugar en la pila que sepan que no será desempilado hasta que esos hilos hayan terminado), y si los apuntadores pasados a cada hilo son a objetos <em>distintos</em>, y si esos objetos solo son usados por un hilo a la vez, no tendrán problemas.</p>
                                <p>En cambio, si los apuntadores son todos a un mismo objeto, tendrán problemas.</p>
                        </li>
                        <li id="p9">
                                <h3>Formas alternativas de desreferencia no‐trivial</h3>
                                <h4>Pregunta</h4>
                                <blockquote>
                                        <p>Luego cuando voy a recuperar los datos en <code>iterar</code> (la funcion que ejecuta el hilo) los recupero de la siguiente manera:</p>
                                        <blockquote>
<pre><code><![CDATA[
int atraccion = *((int *)(datos));
int capacidad = *((int *)(datos) + 1);
int tiempoUso = *((int *)(datos) + 2);
]]></code></pre>
                                        </blockquote>
                                        <p>donde la firma de la funcion es la siguiente: <code>void *iterar(void *datos)</code></p>
                                </blockquote>
                                <h4>Respuesta</h4>
                                <p>Perfecto.</p>
                                <p>También sirve así, que es exactamente equivalente pero resulta bastante más obvio:</p>
                                <blockquote>
<pre><code><![CDATA[
#define DATOS ((int *)datos)
int atraccion = DATOS[0];
int capacidad = DATOS[1];
int tiempoUso = DATOS[2];
]]></code></pre>
                                </blockquote>
                                <p>O igual de obvio pero con menos memoria, así:</p>
                                <blockquote>
<pre><code><![CDATA[
#define DATOS ((int *)datos)
#define atraccion (DATOS[0])
#define capacidad (DATOS[1])
#define tiempoUso (DATOS[2])
]]></code></pre>
                                </blockquote>
                                <p>Aunque ya esto es ligeramente distinto, porque ya no son variables locales con copias de los valores en el arreglo a cuyo inicio apunta lo que fue pasado como parámetro, sino que se usa directamente el arreglo apuntado. Si todo está bien, no habría diferencia: igual el arreglo apuntado por el parámetro de cada hilo debería ser totalmente independiente del usado con los demás hilos. Y si no es así, tendrán problemas.</p>
                                <p>También está el problema de que esos símbolos tomarían ese significado en todo el archivo (más precisamente, en toda la unidad de traducción, que incluye a todos los archivos incluidos), así que si usan esos nombres en cualquier otra parte del código, pasarían cosas malas. Todo depende de cuánto les agrade usar macros.</p>
                                <p>En cualquier caso, la forma que usaron está bien.</p>
                        </li>
                        <li id="p10">
                                <h3>Pasaje de parámetros por referencia reutilizada</h3>
                                <h4>Pregunta</h4>
                                <blockquote>
                                        <p>pero resulta que la funcion siempre recibe los ultimos datos enviados en el <code>pthread_create</code>, NO toma en cuenta los primeros, parece que los perdiera.</p>
                                </blockquote>
                                <h4>Respuesta</h4>
                                <p>Porque seguramente no están creando un objeto independiente para pasar los datos a cada hilo, sino que en cada iteración del ciclo de creación de hilos están <em>sobreescribiendo</em> los datos pasados al anterior. Recuerden que la función recibe un <em>apuntador</em>; es decir, el pasaje de parámetros es por <em>referencia</em>, no por valor. Los valores en su arreglo <strong>no</strong> se copian al momento de llamar a <code>pthread_create</code>.</p>
                        </li>
                        <li id="p11">
                                <h3>Sobreescritura sin sincronización</h3>
                                <h4>Pregunta</h4>
                                <blockquote>
                                        <p>He verificado que los datos son enviados distintos (con un <code>printf</code>) antes del <code>pthread_create</code>. ¿Qué puede estar sucediendo?</p>
                                </blockquote>
                                <h4>Respuesta</h4>
                                <p>Que no hay sincronización entre el momento en el que los datos del hilo son sobreescritos y el momento en el que el hijo copia esos datos a sus variables locales. La mejor manera de resolver el problema es tener objetos separados para el pasaje de parámetros a cada hilo; otra manera bastante más complicada y que no ahorra casi nada es usar primitivas de sincronización para sincronizar esos dos eventos, pero realmente no vale la pena hacerlo así por ahorrarse tres enteros por hilo.</p></li>
                        </li>
<!--
                        <li id="p12">
                                <h3></h3>
                                <h4>Pregunta</h4>
                                <blockquote>
                                </blockquote>
                                <h4>Respuesta</h4>
                        </li>
-->
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
