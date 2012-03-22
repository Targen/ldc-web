<?php
        // Content negotiation? #merwebo.
        // XHTML ftw; una solución decente requeriría una configuración de Apache decente.
        header('Content-Type:application/xhtml+xml;charset=utf-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
        <head>
                <meta http-equiv="Content-type" content="application/xhtml+xml;charset=utf-8"/>
                <title>Notas de Manuel Gómez sobre CI3825 — Consultas del segundo proyecto</title>
                <style type="text/css">p { text-align:justify; }</style>
        </head>

        <body>
                <h1><a href="../../../..">Manuel Gómez</a> — <a href="../../..">Cursos</a> — <a href="../..">CI3825</a> — <a href="..">Enero–Marzo de 2012</a></h1>
                <hr/>
                <h2>2012‒03‒18 (semana 7): Consultas del segundo proyecto</h2>
                <p>Acá les dejo las preguntas y respuestas de varias consultas que me han hecho estudiantes del curso sobre el segundo proyecto.  Espero que les sirvan.</p>
                <ol>
                        <li><a href="#p1">Orden de las reglas en los <code>Makefile</code>s                                     </a></li>
                        <li><a href="#p2">Movimiento del cursor de un archivo abierto                                           </a></li>
                        <li><a href="#p2">Estrategias para generación de los <code>Makefiles</code> con un orden específico     </a></li>
                        <li><a href="#p4">Requerimientos sobre archivos <code>.d</code> generados                               </a></li>
                        <li><a href="#p5">Significado y ubicación de reglas para el <em>target</em> especial <code>.PHONY</code></a></li>
                        <li><a href="#p6">Orden de las reglas en un <code>Makefile</code>                                       </a></li>
                        <li><a href="#p7">Técnicas de copia de archivos y <code>mmap</code>                                     </a></li>
                </ol>
                <ol>
                        <li id="p1">
                                <h3>Movimiento del cursor de un archivo abierto</h3>
                                <h4>Pregunta</h4>
                                <blockquote>
                                        <ol>
                                                <li><p>Buenas noches Manuel, disculpa la molestia, te escribo para ver como puedo mover el offsett de un descriptor que fue abierto con open</p></li>
                                        </ol>
                                </blockquote>
                                <h4>Respuesta</h4>
                                <ol>
                                        <li><p>Si están usando las primitivas de bajo nivel para la manipulación de archivos (<code>open</code>, <code>read</code>, <code>write</code>, <code>fcntl</code>, etc), usarían <code>lseek</code>.</p></li>
                                        <li><p>Si están usando las funciones de la biblioteca estándar de C para manipulación de archivos (<code>fopen</code>, <code>fprintf</code>, <code>fscanf</code>, <code>feof</code>, etc), usarían <code>fseek</code>.</p></li>
                                </ol>
                        </li>
                        <li id="p2">
                                <h3>Orden de las reglas en los <code>Makefile</code>s</h3>
                                <h4>Pregunta</h4>
                                <blockquote>
                                        <ol>
                                                <li><p>ya que para el proyecto es necesario colocar al principio en el makefile algunas cosas</p></li>
                                        </ol>
                                </blockquote>
                                <h4>Respuesta</h4>
                                <ol>
                                        <li><p>Los ejemplos publicados junto con el enunciado, tanto en su sección introductoria como en el paquete de código de ejemplo distribuido con el documento de especificación del proyecto, siguen en efecto un cierto formato en sus Makefiles que se deriva de razones pedagógicas y de consistencia.</p></li>
                                        <li><p>Sin embargo, el enunciado del proyecto no establece requerimientos sobre el código de los Makefiles generados más allá de que deben funcionar para el fin especificado y contener las directivas que sean necesarias para funcionar con el mecanismo de recursión y de archivos de colección por directorio.</p></li>
                                        <li><p>En particular, el enunciado no especifica que las reglas deban ocurrir en los Makefiles generados en algún orden específico.</p></li>
                                </ol>
                        </li>
                        <li id="p3">
                                <h3>Estrategias para generación de los Makefiles con un orden específico</h3>
                                <h4>Pregunta</h4>
                                <blockquote>
                                        <ol>
                                                <li><p>pero que dependen de unas que se hicieron mas abajo entonces luego de escribir en el archivo debo modificar algunas cosas pero debo hacerlo en el comienzo del archivo, como puedo hacer esto ? Es que solo consegui informacion sobre como mover el offset para descriptores del tipo FILE.</p></li>
                                        </ol>
                                </blockquote>
                                <h4>Respuesta</h4>
                                <ol>
                                        <li><p>Si quieren generar los <code>Makefile</code>s con el mismo formato de los ejemplos (que, insisto, no es necesario según los requerimientos formales del enunciado), se me ocurren varias estrategias:</p></li>
                                        <li>
                                                <ol>
                                                        <li><p>Determinen las dependencias de cada archivo generándolas todas en un solo archivo temporal que podrían crear con alguna de las funciones de la familia de mkstemp (pero que sean de las seguras, con “s”). Luego creen el <code>Makefile</code>, escriben la primera parte, y copian el contenido del archivo temporal al final del <code>Makefile</code>. Idealmente deberían borrar el archivo temporal al terminar de usarlo. La creación de archivos temporales trae ciertas complicaciones que no son inmediatamente evidentes, así que no recomiendo muy fuertemente esta técnica.</p></li>
                                                        <li><p>Apliquen la misma técnica anterior pero guardando las dependencias generadas de cada archivo en archivos separados creados por el compilador (idealmente, los “.d”). Esto es más sencillo porque no es necesario crear un archivo temporal, pero deberán leer muchos archivos en vez de uno solo. Esta técnica es la que tenía en mente al redactar el documento de especificación del proyecto, pero en efecto es más compleja y que la que ustedes intentan aplicar, que no es menos válida.</p></li>
                                                        <li><p>Hagan varias pasadas sobre la estructura de directorios: por ejemplo, la primera para determinar qué archivos y subdirectorios existen (y así generar la primera parte del <code>Makefile</code>), y otra para generar las dependencias al final del <code>Makefile</code>. Los criterios de corrección oficiales de las asignaciones anteriores del laboratorio han incluido que se haga una sola pasada por los datos, así que no recomiendo esta técnica.</p></li>
                                                        <li><p>No generen las dependencias a archivos (sea por archivos temporales explícitos o por la salida estándar redirigida a un archivo), sino por la salida estándar redirigida a un pipe que va a cada proceso de rautomake. Esta técnica es sin duda alguna la más eficiente (ya que no depende de manipulación de archivos entre varios procesos, que es ineficiente por todo lo que saben de la teoría), pero la comunicación a través de pipes podría resultar incómoda, sobre todo porque no habría garantía alguna de atomicidad de las operaciones de lectura y escritura.</p></li>
                                                        <li><p>Generen el archivo con el orden indeseado, y luego léanlo a memoria e imprímanlo en el orden que desean. Esto implica escribir código para reconocer hasta cierto punto el formato del archivo generado, así que podría ser complejo, y además es ineficiente tanto en tiempo como en memoria. No recomiendo esta técnica.</p></li>
                                                </ol>
                                        </li>
                                        <li><p>Cualquiera de estas técnicas sirve para hacer lo que quieren hacer con varios grados de eficiencia, dificultad y robustez, pero insisto otra vez: aunque sería bonito porque los <code>Makefile</code>s generados serían más legibles, el enunciado NO requiere orden alguno entre las reglas del <code>Makefile</code> generado siempre que funcione como debe.</p></li>
                                        <li><p>La única regla de Make en relación al orden que creo que les puede interesar es que la primera regla en aparecer es la que se activa por defecto. Es posible ponerla sin dependencias al principio y especificar sus dependencias luego. Por ejemplo, este <code>Makefile</code> compila la primera tarea si su código está todo en reportlog.c:</p></li>
                                        <li><blockquote>
<pre><code><![CDATA[
all:
reportlog: reportlog.c
all: reportlog
]]></code></pre>
                                        </blockquote></li>
                                        <li><p>Omití las recetas porque las reglas implícitas de GNU Make hacen lo que deben hacer en este caso.</p></li>
                                </ol>
                        </li>
                        <li id="p4">
                                <h3>Requerimientos sobre archivos <code>.d</code> generados</h3>
                                <h4>Pregunta</h4>
                                <blockquote>
                                        <ol>
                                                <li><p>Te escribo para comentarte una duda que tengo.. Al usar la opcion de gcc -MMD, sabes que se crea un archivo .d, no hay problema con que esos archivos queden dentro del proyecto de software, en el ejemplo dentro de las carpertas de multidoc??</p></li>
                                        </ol>
                                </blockquote>
                                <h4>Respuesta</h4>
                                <ol>
                                        <li><p>El documento del enunciado del proyecto especifica esto en §2.2.b:</p></li>
                                        <li>
                                                <blockquote>
                                                        <p>rautomake podrá suponer que no existe ningún archivo dentro del directorio donde es ejecutado, ni dentro de ninguno de sus subdirectorios, cuyo nombre termine en “.d”. Rautomake no será ejecutado en un directorio que tenga algún subdirectorio directo o indirecto cuyo nombre termine en “.d”.</p>
                                                </blockquote>
                                        </li>
                                        <li><p>El propósito de ese texto es asegurarles que cualquier archivo con un nombre así puede ser descartado; es decir, que no es parte del proyecto que debe poder compilarse con sus Makefiles generados, y no es necesario para su funcionamiento, porque en principio no existían; si ya existen, están en libertad de sobreescribirlos, borrarlos o lo que sea. Así se les permite usar los archivos .d para obtener las dependencias. Hay otros métodos, pero ese es uno que es perfectamente válido usar (de hecho es el que a mí se me había ocurrido, pero varios han descubierto soluciones alternativas también válidas, y en algunos casos un poco mejores y más simples).</p></li>
                                        <li><p>El enunciado no establece requerimientos sobre los archivos que su proyecto genere aparte de que se deben crear Makefiles en ciertas condiciones, y que el proyecto sobre el que corre debe poder compilarse.</p></li>
                                        <li><p>Hubiera sido bueno especificar que no puede modificar ningún archivo que el proyecto tuviera originalmente (con la excepción especial de los .d, porque con esos pueden hacer lo que quieran), pero se me pasó. Pero claro, si borran el proyecto entonces no podrían al final compilarlo con sus Makefiles.</p></li>
                                </ol>
                        </li>
                        <li id="p5">
                                <h3>Significado y ubicación de reglas para el <em>target</em> especial <code>.PHONY</code></h3>
                                <h4>Pregunta</h4>
                                <blockquote>
                                        <ol>
                                                <li><p>Es necesario que el PHONY este al principio del makefile para que este funcione??</p></li>
                                        </ol>
                                </blockquote>
                                <h4>Respuesta</h4>
                                <ol>
                                        <li><p>El <em>target</em> especial <code>.PHONY</code> de GNU Make hace que la evaluación de las recetas de sus prerequisitos se efectúe siempre que ocurra en una regla que se procesa sin que se intente ver si hay un archivo que se llama como ese <em>target</em> para verificar si está al día.</p></li>
                                        <li>
                                                <p>La idea es más o menos esta: en un <code>Makefile</code> se suele incluir, por ejemplo, un <em>target</em> llamado <code>clean</code> cuya receta elimina todos los archivos del proyecto que no sean parte de su código fuente. El detalle está en que no se pretende considerar que pueda haber un archivo cuyo <strong>nombre</strong> sea <code>clean</code>, en contraste con las reglas usuales para compilar. Por ejemplo, en la regla</p>
                                                <blockquote>
<pre><code><![CDATA[
parser.o: parser.c
	$(CC) $(CFLAGS) -c $<
]]></code></pre>
                                                </blockquote>
                                                <p>se le indica a Make que si el archivo llamado <code>parser.o</code> es más viejo que el archivo <code>parser.c</code>, o si <code>parser.o</code> no existe, entonces se debe (re)generar ejecutando la receta. El cambio, en el caso de la regla</p>
                                                <blockquote>
<pre><code><![CDATA[
clean:
	rm -f ./*.o multidoc
]]></code></pre>
                                                </blockquote>
                                                <p>no se desea indicarle a Make que verifique si hay un archivo llamado <code>clean</code> que quizás deba actualizarse ejecutando la receta, sino que se desea ejecutar el comando incondicionalmente. Para eso es que se usa el <em>target</em> especial <code>.PHONY</code>.</p>
                                        </li>
                                        <li><p>Como se desea hacer la entrada a los subdirectorios incondicionalmente (porque solo ellos sabrán si algo debe regenerarse, porque solo ellos conocen las dependencias de los archivos que están en ellos), es necesario que las ejecuciones recursivas se ejecuten incondicionalmente (aunque en el enunciado final eliminaron lo que había escrito que requería eso, pero seguramente será evaluado; ya saben cómo es esto…).</p></li>
                                        <li><p>Aunque <code>.PHONY</code> es un <em>target</em> especial que tiene una semántica distinta de la de los <em>target</em>s usuales, sus reglas, al igual que todas las demás reglas, pueden ocurrir en el Makefile en cualquier orden.</p></li>
                                </ol>
                        </li>
                        <li id="p6">
                                <h3>Orden de las reglas en un <code>Makefile</code></h3>
                                <h4>Pregunta</h4>
                                <blockquote>
                                        <ol>
                                                <li><p>-El orden del resto de las reglas que se colocan en el makefile es irrelevante mientras esten correctas?</p></li>
                                        </ol>
                                </blockquote>
                                <h4>Respuesta</h4>
                                <ol>
                                        <li><p>En efecto. Make lee el <code>Makefile</code> completo y <strong>luego</strong> hace el análisis sobre el grafo de dependencias resultante, así que el orden no importa si el Makefile es correcto en algún orden.</p></li>
                                </ol>
                        </li>
                        <li id="p7">
                                <h3>Técnicas de copia de archivos y <code>mmap</code></h3>
                                <h4>Pregunta</h4>
                                <blockquote>
                                        <ol>
                                                <li><p>Tengo otra duda: Para copiar lo que esta en un archivo a otro.. Tendria que leer de uno y escribir en el otro.</p></li>
                                                <li><p>Pero como podria hacer para saber el tamano de lo que voy a leer(en realidad quiero leer toda la linea)?... Podria leer caracter por caracter pero alguna vez nos dijeron que eso no era bueno..entonces no se..</p></li>
                                        </ol>
                                </blockquote>
                                <h4>Respuesta</h4>
                                <ol>
                                        <li><p>En efecto leer caracter por caracter es una opción indeseable: es decirle al sistema de operación que bloquee el proceso hasta que sea leído ese caracter, y luego se requiere pasar a estado <em>running</em> únicamente para pedirle al sistema operativo que escriba ese caracter en el otro archivo, todo eso por cada uno de los caracteres del archivo. Esto es lentísimo por la cantidad de veces que el proceso se bloquea.</p></li>
                                        <li><p>La técnica sencilla y básica para mejorar esto es leer por lotes: su programa puede tener un espacio de memoria (un <em>buffer</em>) de un cierto tamaño (probablemente fijo) que se use para almacenar temporalmente una parte del archivo que se leyó, y así poder escribir el contenido de ese <em>buffer</em> cuando se termina de leer un lote de los datos a copiar, y luego volver a empezar. En el caso de un <em>buffer</em> de exactamente un caracter, esta técnica, claro, se reduce a la anterior y es lenta.</p></li>
                                        <li><p>Otra técnica es determinar el tamaño del archivo a copiar usando funciones del sistema de archivo que provean esa información (como <code>stat</code>) y crear un <em>buffer</em> en la memoria del programa suficientemente grande para almacenar su contenido completo. Este es, claro, el extremo opuesto de la primera técnica. Normalmente es la técnica más rápida (<em>modulo</em> el impacto de la paginación de la memoria y demás), pero consume cantidades de memoria que podrían ser excesivas, y se arriesga a que el proceso falle por tratar de manipular un archivo muy grande y que se agote la memoria del sistema.</p></li>
                                        <li><p>Existen técnicas más modernas que resuelven el problema transfiriéndolo al sistema de operación, que seguramente sabrá aproximarse mejor al óptimo: es posible hacer un <em>mapeo</em> del contenido de un archivo al espacio de memoria de un proceso; cuando el proceso desea leer de o escribir a una posición de memoria que representa una posición en un archivo, el sistema de operación se encarga de traducir eso en la acción correspondiente sobre el archivo <em>mapeado</em>. Otra ventaja de esta técnica es que el código usado para acceder a una posición de un archivo se convierte en un simple acceso a un arreglo en la memoria como cualquier otro, así que la programación se facilita cuando las operaciones hechas sobre el archivo son de cierta complejidad. La desventaja es que hay que aprender a usar estos <em>mapeos</em> de memoria y su semántica puede ser un poco difícil de asimilar en un principio (pero, claro, eso se aprende una sola vez y de ahí en adelante simplifica la vida permanentemente).</p></li>
                                        <li><p>Como el enunciado no establece requerimientos de eficiencia (y realmente son pequeños los datos que hay que copiar) pueden hacerlo como quieran.</p></li>
                                        <li><p>La técnica de <em>mapeos</em> de memoria es algo que vale la pena aprender, así que se las recomiendo si tienen algo de tiempo para dedicarle; si terminan dedicándose a hacer software sujeto a restricciones de eficiencia o que sea de escala masiva (básicamente cualquier cosa que valga la pena hacer fuera del mundo académico), les será necesario saber trabajar con eso. Si tienen poco tiempo, recuerden que la técnica de copiar un byte a la vez es válida y simple, pero no les enseñará absolutamente nada. La técnica del <em>buffer</em> es casi tan simple como la de copiar un byte a la vez, y claro, es casi tan aburrida.</p></li>
                                        <li><p>Si deciden usar <em>mapeos</em> de memoria, lean sobre la función <code>mmap</code>. No es nada difícil de usar. El único detalle es que no es buena idea (y por lo general) usarlas para operaciones que puedan modificar el tamaño del archivo <em>mapeado</em>, así que sería más para la lectura que para la escritura. Por cierto, <code>fprintf(out_file, "%*s", n, map_ptr)</code> lee <code>n</code> caracteres desde la posición de memoria apuntada por <code>map_ptr</code> y los escribe a <code>out_file</code>; si usan eso, la copia sale en muy poco código y será extremadamente eficiente.</p></li>
                                </ol>
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
