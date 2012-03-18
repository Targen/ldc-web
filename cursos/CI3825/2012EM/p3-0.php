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
                        <li><a href="#p1">Orden de las reglas en los <code>Makefile</code>s                   </a></li>
                        <li><a href="#p2">Movimiento del cursor de un archivo abierto                         </a></li>
                        <li><a href="#p2">Estrategias para generación de los Makefiles con un orden específico</a></li>
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
                                                <ul>
                                                        <li><p>Determinen las dependencias de cada archivo generándolas todas en un solo archivo temporal que podrían crear con alguna de las funciones de la familia de mkstemp (pero que sean de las seguras, con “s”). Luego creen el <code>Makefile</code>, escriben la primera parte, y copian el contenido del archivo temporal al final del <code>Makefile</code>. Idealmente deberían borrar el archivo temporal al terminar de usarlo. La creación de archivos temporales trae ciertas complicaciones que no son inmediatamente evidentes, así que no recomiendo muy fuertemente esta técnica.</p></li>
                                                        <li><p>Apliquen la misma técnica anterior pero guardando las dependencias generadas de cada archivo en archivos separados creados por el compilador (idealmente, los “.d”). Esto es más sencillo porque no es necesario crear un archivo temporal, pero deberán leer muchos archivos en vez de uno solo. Esta técnica es la que tenía en mente al redactar el documento de especificación del proyecto, pero en efecto es más compleja y que la que ustedes intentan aplicar, que no es menos válida.</p></li>
                                                        <li><p>Hagan varias pasadas sobre la estructura de directorios: por ejemplo, la primera para determinar qué archivos y subdirectorios existen (y así generar la primera parte del <code>Makefile</code>), y otra para generar las dependencias al final del <code>Makefile</code>. Los criterios de corrección oficiales de las asignaciones anteriores del laboratorio han incluido que se haga una sola pasada por los datos, así que no recomiendo esta técnica.</p></li>
                                                        <li><p>No generen las dependencias a archivos (sea por archivos temporales explícitos o por la salida estándar redirigida a un archivo), sino por la salida estándar redirigida a un pipe que va a cada proceso de rautomake. Esta técnica es sin duda alguna la más eficiente (ya que no depende de manipulación de archivos entre varios procesos, que es ineficiente por todo lo que saben de la teoría), pero la comunicación a través de pipes podría resultar incómoda, sobre todo porque no habría garantía alguna de atomicidad de las operaciones de lectura y escritura.</p></li>
                                                        <li><p>Generen el archivo con el orden indeseado, y luego léanlo a memoria e imprímanlo en el orden que desean. Esto implica escribir código para reconocer hasta cierto punto el formato del archivo generado, así que podría ser complejo, y además es ineficiente tanto en tiempo como en memoria. No recomiendo esta técnica.</p></li>
                                                </ul>
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
