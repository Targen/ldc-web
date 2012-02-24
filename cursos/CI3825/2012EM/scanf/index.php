<?php
        // Content negotiation? #merwebo.
        // No me voy a poner a hacer una versión con los escapes de otra forma solo para gente con browsers chimbos.
        header('Content-Type:application/xhtml+xml;charset=utf-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
        <head>
                <meta http-equiv="Content-type" content="application/xhtml+xml;charset=utf-8"/>
                <title>Notas de Manuel Gómez sobre CI3825 — Sugerencias para la tarea</title>
                <style type="text/css">p { text-align:justify; }</style>
        </head>

        <body>
                <h1><a href="../../../..">Manuel Gómez</a> — <a href="../../..">Cursos</a> — <a href="../..">CI3825</a> — <a href="..">Enero–Marzo de 2012</a></h1>
                <hr/>
                <h2>2012‒01‒28 (semana 3): Sugerencias para la tarea</h2>
                <p>Hace unos días un estudiante del curso me mostró uno de los ejemplos de código publicados en el sistema de información que se está usando para el curso.  Como ese sistema es bastante impráctico y cerrado, copio acá <a href="ejemploscanf.txt">ese archivo de código (<code>ejemploscanf.txt</code>)</a>:</p>
                <blockquote>
<pre><code><![CDATA[
/*
Nombre del Archivo: ejemplofile.c
Función: Un ejemplo muy sencillo para ilustrar la lectura de archivos con el fscanf. 
Autor: M. Curiel
Uso: ejemplofile archivo 
     Se puede probar con el archivo data1 que tiene el formato requerido.

*/


#include <stdio.h>
#define MAXNOMBRE 100


int main(int argc, char *argv[]) 
 {
     char nom[MAXNOMBRE];
     FILE *fp;
     int i, k, edad=0, tiempo=0;


     /* Una forma de leer */
     fp = fopen(argv[1], "r");
     i = 0;

     while (!feof(fp)) {
       fscanf(fp, "%d,%d,%s", &edad, &tiempo, nom);
       printf("%d, %d, %s\n", edad, tiempo, nom);
           
     }
     
  
      fclose(fp); 
      return(0); 



}
]]></code></pre>
                </blockquote>
                <p>Hay varias cosas interesantes en ese código.  Lo primero que resalta es la línea que dice <code>#define MAXNOMBRE 100</code>.  En principio, es bueno usar esta clase de definición: si van a incluir constantes en su código que sean más interesantes que un cero para inicializaciones o comparaciones, un uno para incrementos, o cualquier otro uso <em>sencillo</em> de constantes numéricas, no hay problema con que las incluyan directamente en el código; sin embargo, si van a usar un número en su código que especifique algún parámetro particular del funcionamiento de su programa y no sea algo totalmente trivial, es bueno que utilicen un símbolo para representar ese número.  Las ventajas de hacer esto son diversas, y entre las más importantes está que con buenos símbolos se vuelve evidente el significado de esas constantes, y que si usan ese número en más de un punto del código de su programa, se hace muy sencillo modificarlo en todos los lugares a la vez: solo hace falta modificar la definición del símbolo, que está en un solo punto del código.  Recuerden que <code>#define</code> es una <em>directiva de preprocesamiento</em> cuyo efecto es aplicar la sustitución textual del símbolo definido sobre todo el código al que aplique esa definición.  Las reglas de preprocesamiento de C pueden llegar a ser algo confusas, así que tengan cuidado con definiciones de símbolos que usen otros símbolos, y cualquier otra cosa compleja.  De ser necesario y si da tiempo, puedo hablar más sobre esto en una clase práctica.</p>
                <p>Pero hay un problema con este uso particular del símbolo, y no es por el hecho de haberse usado un símbolo, sino por aquello para lo que fue usado.  Observen que el símbolo se usa para especificar el tamaño de un arreglo de caracteres asociado a una variable local del procedimiento principal del programa: la variable <code>nom</code>.  Ese programa luego <em>intenta</em> abrir un archivo, luego <em>intenta</em> leerlo <em>como si se hubiera abierto exitosamente</em>, y luego de cada <em>intento</em> de lectura, imprime lo que se hubiera leído <em>si la lectura hubiera sido exitosa</em>; finalmente, <em>intenta</em> cerrar un archivo <em>como si se hubiera abierto exitosamente</em>.  Luego vamos con el problema de hacer todas esas suposiciones sin verificarlas; por ahora, asumamos que todas esas operaciones sí se realizaron exitosamente por arte de magia (porque el programa <strong>nunca</strong> se toma el trabajo de verificar esas suposiciones, así que si eso sucede es cuestión de suerte, y asumir que siempre sea así sería cuestión de magia).</p>
                <p>Hay una suposición adicional escondida (aunque no tanto) en el programa: la invocación del procedimiento <code>fscanf</code> indica que se desea intentar leer del archivo referido por el valor apuntado por la variable <code>fp</code>, y debe leerse un entero, una coma, otro entero, otra coma, y luego cualquier secuencia de caracteres.  Excepto que es un poco más complejo que eso.  Para empezar, la especificación de conversión <code>%d</code> no solo especifica a <code>fscanf</code> que debe leer caracteres que representen dígitos decimales y convertirlos a un entero que debe almacenar en la dirección de memoria <code>&amp;edad</code>, sino que deben omitirse todos los <em>espacios en blanco</em> (según la función <code>isspace</code>) que ocurran desde la posición actual en el archivo, y <strong>luego</strong> es que se deben tomar los dígitos.  La mayoría de las especificaciones de conversión (excepto <code>[</code>, <code>c</code>, <code>C</code> y <code>n</code> <a href="http://pubs.opengroup.org/onlinepubs/009695399/functions/fscanf.html">según IEEE 1003.1:2003 (POSIX.1)</a>) tienen este mismo comportamiento de leer y omitir los espacios en blanco desde la posición actual, y luego leer lo que les interesa.</p>
                <p>El problema acá es que se acostumbra a que los archivos de entrada tengan al final la secuencia que indica que se alcanzó un fin de línea.  Es una costumbre tan fuerte que todos los editores de texto buenos tienen activada por defecto una opción que añade esa secuencia al final del archivo en caso de que no exista al momento de guardar un texto editado, y la razón es que muchos programas que utilizan texto como entrada asumen que todas las líneas del archivo tienen un terminador de línea al final, incluyendo la última.  <a href="data1">El archivo de entrada que acompaña a este código</a> de hecho incluye el terminador de línea final.  Pero fíjense en lo que pasa: la especificación de conversión <code>%s</code> consume e ignora espacios desde la posición actual en el archivo cuando comienza su acción, y luego consume y guarda todos los caracteres que encuentre hasta el primer espacio en la posición de memoria apuntada por <code>nom</code>.  Pero hasta ahí llega; si en efecto se lee bien el archivo de entrada, la última línea se leerá hasta su terminador de línea, <strong>no inclusive</strong>.  Luego se imprimen sus datos, y el ciclo verifica si se llegó al final del archivo.  Como <strong>no</strong> se ha llegado al final del archivo porque <em>aun no se ha leído el terminador de línea de la última línea</em>, el ciclo vuelve a entrar; <code>fscanf</code> falla porque no tiene nada que leer (la primera conversión <code>%d</code> consume el espacio en blanco y se llega al final del archivo antes de encontrar dígitos decimales), y se vuelve a imprimir lo que se había leído a las variables en la iteración anterior.  El programa reporta la última línea dos veces.</p>
                <p>La solución es extremadamente sencilla: se agrega un espacio al final del string de formato.  Así el espacio en blanco que separa una línea de otra se lee luego de guardar los datos de la línea anterior.  Recuerden que un espacio en blanco en el string de formato de cualquiera de las funciones de la familia <code>scanf</code> <strong>es una directiva</strong> que especifica que se debe consumir todo el espacio en blanco desde ese punto en adelante hasta el primer caracter en la entrada que no sea un espacio en blanco.</p>
                <p>Pero hay otro problema <strong>mucho</strong> más grave: la conversión <code>%s</code> lee al arreglo <code>nom</code> todo lo que haya al final de cada línea que no sea espacios en blanco, pero el arreglo <code>nom</code> solo tiene espacio para <code>MAXNOMBRE</code> caracteres (específicamente, 100), incluyendo el byte nulo que indica el final del string.  Si la entrada tuviera al final de una línea una secuencia de caracteres que no sean espacios que sea más larga que eso, <code>fscanf</code> guardará caracteres en espacios de memoria que traspasan la frontera del arreglo y corresponden a otras variables, o a estructuras de control en la pila de ejecución, o cualquier cosa.  <big><strong>Esto es un error gravísimo.</strong></big>  Esta clase de error se conoce como un <em>buffer overflow</em> (y como se trata de un objeto en la pila de ejecución, porque es una variable local, es también un <em>stack buffer overflow</em> o un <em>stack smash</em>).  Los <em>buffer overflows</em> son la causa más común de problemas de seguridad informática en el mundo de la computación, y casi todas las veces que un programa se guinda y se cierra, es culpa de esta clase de error.</p>
                <p>El problema radica en que <code>fscanf</code> no tiene manera de saber de qué tamaño es el arreglo <code>nom</code> porque lo único que conoce es dónde comienza.  La solución ingenua es decirle a <code>fscanf</code> hasta qué punto es seguro que escriba datos, y así sabrá cuándo detenerse.  Esto haría que el programa sea <em>seguro</em> (suponiendo otro montón de cosas), pero no podría ser suficientemente <em>flexible</em> ni <em>escalable</em> para manejar nombres de tamaño mayor que ese número mágico, cien, que es totalmente arbitrario.  Para aplicar esta solución, en vez de usar la especificación de conversión <code>%s</code>, usamos <code>%99s</code>: la documentación de la conversión <code>s</code> de <code>fscanf</code> dice que ese número (que es y no debería ser opcional) es el número máximo de caracteres que podrá almacenarse en el espacio a cuyo inicio apunta el valor suministrado en el parámetro correspondiente a esa conversión, pero sin incluir el caracter nulo, que también se debe escribir; por eso 99 y no 100.  Claro: ahora hay que definir dos símbolos:</p>
                <blockquote>
<pre><code><![CDATA[
#include <stdio.h>
#define MAXNOMBRE 100
#define MAXNOMBRESCAN "99"


int main(int argc, char *argv[]) 
 {
     char nom[MAXNOMBRE];
     FILE *fp;
     int i, k, edad=0, tiempo=0;


     /* Una forma de leer */
     fp = fopen(argv[1], "r");
     i = 0;

     while (!feof(fp)) {
       fscanf(fp, "%d,%d,%" MAXNOMBRESCAN "s ", &edad, &tiempo, nom);
       printf("%d, %d, %s\n", edad, tiempo, nom);
           
     }
     
  
      fclose(fp); 
      return(0); 



}
]]></code></pre>
                </blockquote>
                <p>Si quieren que su programa sea un poco más flexible y escalable, sería mejor que no dependieran en absoluto de constantes mágicas y tamaños fijos para sus estructuras de datos.  La manera más cómoda de hacer esto con las herramientas de las que disponen es indicarle a <code>fscanf</code> que él mismo debería reservar en memoria el espacio para los caracteres que lee, y que la información que escribe sobre nuestras variables no deberían ser los propios caracteres leídos, sino <em>la dirección de la memoria que él reservó para los caracteres que él leyó</em>.  La versión de <code>fscanf</code> incluida en la implementación de la biblioteca estándar de C del proyecto GNU (que es lo que ustedes usan, porque están usando las herramientas de GCC) permite usar el modificador <code>m</code> para las conversiones <code>[</code>, <code>c</code> y <code>s</code> de la familia de funciones <code>scanf</code> para especificar precisamente esto.  En vez de la especificación de conversión <code>%99s</code>, usaremos la especificación de conversión <code>%ms</code>.  En vez de reservar un arreglo de cien entradas de tipo caracter en la pila de ejecución (que fue lo que hicimos al escribir <code>char nom[MAXNOMBRE];</code>, reservaremos espacio para un <em>apuntador</em> a un caracter en alguna parte de la memoria: <code>char * nom;</code>.  En vez de pasarle a <code>fscanf</code> la dirección donde comienza el arreglo que deberá llenar (<code>nom</code>), le pasaremos la dirección de nuestra variable de tipo apuntador, para que <code>fscanf</code> guarde en ella la dirección del inicio del espacio de memoria que reservó para los datos que leyó: <code>&amp;nom</code>.  Puede ser complicado entenderlo, pero es natural: pensar con apuntadores es complicado.  Quedaría algo así:</p>
                <blockquote>
<pre><code><![CDATA[
#include <stdio.h>
#include <stdlib.h>


int main(int argc, char *argv[]) 
 {
     char * nom;
     FILE *fp;
     int i, k, edad=0, tiempo=0;


     /* Una forma de leer */
     fp = fopen(argv[1], "r");
     i = 0;

     while (!feof(fp)) {
       fscanf(fp, "%d,%d,%ms ", &edad, &tiempo, &nom);
       printf("%d, %d, %s\n", edad, tiempo, nom);
       free(nom);
           
     }
     
  
      fclose(fp); 
      return(0); 



}]]></code></pre>
                </blockquote>
                <p>Hay algo adicional: como <code>fscanf</code> reservó un espacio en memoria dinámica (en el <em>heap</em>) para los datos que leyó, debemos <em>liberar</em> esta memoria cuando hayamos terminado de usarla usando la función <code>free</code> que está declarada en el encabezado <code>stdlib.h</code> de la biblioteca estándar de C.  Si simplemente seguimos a la próxima iteración y sobreescribimos la dirección del espacio reservado por <code>fscanf</code> en la iteración anterior, no tendremos manera de utilizar esos datos (porque ya no sabremos dónde están) y tampoco podremos liberar el espacio (porque para liberarlos debemos pasarle a <code>free</code> la dirección donde comienzan los datos que deseamos liberar.  Este problema se denomina <em>goteo de memoria</em> y es la razón por la que un navegador Web con tres o cuatro páginas abiertas puede llegar a consumir cientos y cientos de millones de bytes de memoria si han sido usados por varias horas: a los programadores malos se les olvida liberar la memoria que reservan dinámicamente, y esa memoria termina acumulándose y abultando los procesos que persisten en el sistema con espacio desperdiciado e irrecuperable.  Recuerden usar <code>free</code>.</p>
                <p>Pero sigue habiendo un montón de problemas.  ¿Recuerdan todo el asunto sobre las suposiciones que el programa hacía sobre el resultado de <em>intentar</em> realizar ciertas acciones?  Este programa es extremadamente básico y <strong>formalmente incorrecto</strong>: no incluye verificación alguna de errores, así que es completamente inseguro y su comportamiento solo es el que se deseaba programar en un subconjunto ideal de los casos posibles de ejecución.  Es <strong>extremadamente importante</strong> que no programen de esta manera si pretenden escribir software de calidad razonable.  El primer problema evidente es que se utiliza la posición 1 (que es la segunda) del arreglo de argumentos de línea de comando <code>argv</code> sin verificar si en efecto existen al menos dos elementos en ese arreglo.  Luego se intenta abrir el archivo cuyo nombre (y camino) está en ese argumento de línea de comando sin verificar si en efecto se logró abrir el archivo exitosamente.  Luego se intenta leer ciertos datos con un cierto formato sin verificar que la entrada realmente contenía toda la información que se esperaba en el formato indicado.  Ese uso de <code>free</code> también es muy peligroso: si todo va bien para una línea pero la siguiente no tiene el formato correcto, al final de la iteración de la correcta se libera el espacio reservado para el nombre indicado en esa línea, pero al encontrar un error en la siguiente, probablemente no se reservará espacio para el nombre porque <code>fscanf</code> fallaría antes de lograr leer el nombre; a pesar de esto, como no se verifican los errores, la ejecución continuaría, imprimiría algo (quién sabe qué) y luego intentaría liberar la memoria apuntada por <code>nom</code>, pero esa memoria ya fue liberada.  Intentar liberar memoria que ya fue liberada anteriormente, o hacer un <em>double free</em>, producirá un error a tiempo de ejecución que abortará al programa.</p>
                <p>Todos estos problemas <strong>deben</strong> arreglarse para que el flujo de ejecución del programa esté completamente bajo el control del programador y sea predecible y correcto.  Cada uno de esos problemas (y otros más) se resuelven leyendo la documentación de las funciones o mecanismos que son capaces de producir condiciones de error, y escribiendo un programa que maneje todos esos casos de alguna manera razonable.  <a href="final.c">El programa final corregido</a> (con algunas mejoras de flexibilidad) es éste:</p>
                <blockquote>
<pre><code><![CDATA[
#include <stdio.h>
#include <stdlib.h>
#include <errno.h>
#include <sysexits.h>

int main(int argc, char *argv[]) {
        char * nom;
        FILE *fp;
        int i, k, edad = 0, tiempo = 0;

        if (argc != 2) {
                puts("Se debe pasar exactamente un argumento.");
                exit(EX_USAGE);
        }

        /* Una forma de leer */
        fp = fopen(argv[1], "r");
        if (fp == NULL) {
                perror("El archivo no existe; fopen");
                exit(EX_USAGE);
        }

        i = 0;
        int n;
        while (!feof(fp)) {
                errno = 0;
                n = fscanf(fp, "%d ,%d ,%ms ", &edad, &tiempo, &nom);
                if (n == EOF) {
                        if (errno != 0) {
                                perror("Hubo un error de lectura; fscanf");
                                exit(EX_IOERR);
                        } else {
                                fprintf(stderr, "El formato de esta línea está malo al final.\n");
                                exit(EX_DATAERR);
                        }
                }

                if (n != 3) {
                        fprintf(stderr, "El formato de esta línea está mal; solo pude hacer %d conversiones.\n", n);
                        exit(EX_DATAERR);
                }

                printf("%d, %d, %s\n", edad, tiempo, nom);
        }

        fclose(fp);
        return 0;
}
]]></code></pre>
                </blockquote>
                <p>Ese programa se aproxima mucho más a lo que ustedes deben hacer en la tarea y en sus proyectos.  A veces es engorroso programar verificando todos los errores con tanta rigurosidad, pero así debe ser la programación.  Hay otros lenguajes de programación que proveen atajos que hacen que estas cosas se expresen de maneras diferentes que a veces pueden parecer más sencillas (por ejemplo, implementando manejo de excepciones), pero al final terminan haciendo lo mismo: <big><strong>todos los flujos de ejecución del programa deben ser el resultado del diseño del programador</strong></big>.</p>
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
