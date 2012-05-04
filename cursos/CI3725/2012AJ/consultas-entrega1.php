<?php
        // Content negotiation? #merwebo.
        // XHTML ftw; una solución decente requeriría una configuración de Apache decente.
        header('Content-Type:application/xhtml+xml;charset=utf-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
        <head>
                <meta http-equiv="Content-type" content="application/xhtml+xml;charset=utf-8"/>
                <title>Notas de Manuel Gómez sobre CI3725 — Consultas de la primera entrega del proyecto</title>
                <style type="text/css">p { text-align:justify; }</style>
        </head>

        <body>
                <h1><a href="../../..">Manuel Gómez</a> — <a href="../..">Cursos</a> — <a href="..">CI3725</a> — <a href=".">Abril–Julio de 2012</a></h1>
                <hr/>
                <h2>2012‒03‒18 (semana 7): Consultas de la primera entrega del proyecto</h2>
                <p>Acá les dejo las preguntas y respuestas de varias consultas que me han hecho estudiantes del curso sobre el segundo proyecto.  Espero que les sirvan.</p>
                <ol>
                        <li><a href="#pregunta1">Lectura de entrada y mecanismo de ejecución</a></li>
                        <li><a href="#pregunta2">Tipos de datos para los <em>token</em>s    </a></li>
                </ol>
                <ol>
                        <li id="pregunta1">
                                <h3>Lectura de entrada y mecanismo de ejecución</h3>
                                <h4>Pregunta</h4>
                                <blockquote>
                                        <ol>
                                                <li><p>La primera es como se le va a dar el programa escrito en AsGArD al analizador lexicográ fico, por lo que entendí vamos a ser un script, me imagino que uno muy parecido a los que se hacen en sistemas de operación I, que después de ser llamado, nos pedirá que introduzcamos linea por linea en la consola el código en AsGArD. Es asi? o vamos a leer el codigo de un archivo?</p></li>
                                        </ol>
                                </blockquote>
                                <h4>Respuesta</h4>
                                <ol>
                                        <li><p>Ni lo uno ni lo otro. En la sección “Entrega de la Implementación” del enunciado de la etapa 1 del proyecto dice esto:</p></li>
                                        <li><blockquote>
                                                <p>El analizador deberá ser ejecutado con el comando “./LexAsgard”, por lo que es posible que tenga que incorporar un script a su entrega que permita que la llamada a su programa se realice de esta forma. Note que la entrada para su programa será a través de la entrada estándar del sistema de operación. Sin embargo, deben abstenerse de imprimir nada más que lo pedido y tener cuidado con que la salida del programa corresponda con fidelidad a los requisitos mencionados anteriormente.</p>
                                        </blockquote></li>
                                        <li><p>Cualquier compilador de C++ es capaz de producir ejecutables nativos, así que luego de compilar su proyecto (con el comando <code>make</code>, claro, así que haría falta un Makefile) se podría ejecutar <code>./LexAsgard</code> porque ese sería el nombre del ejecutable generado. Esencialmente lo mismo aplica para Haskell, aunque el proceso de compilación de Haskell podría hacerse con herramientas mucho mejores que Make.</p></li>
                                        <li><p>Ruby y Python son básicamente lenguajes interpretados (al menos en sus implantaciones canónicas, que son las recomendadas), y el mejor, más fácil y más común mecanismo para hacer que un <em>script</em> en cualquiera de los dos lenguajes pueda ejecutarse directamente es usar el mecanismo del <em>shebang</em>.</p></li>
                                        <li><blockquote>
<pre><code><![CDATA[
#!/camino/a/un/comando argumento1 argumento2 argumento3
]]></code></pre>
                                        </blockquote></li>
                                        <li><p>entonces lo que se ejecuta en realidad es el ejecutable mencionado en esa línea, que se denomina <em>shebang</em>, con los argumentos que aparecen en esa línea (y podría haber cualquier cantidad de argumentos, incluso ninguno, hasta el límite del sistema de operación que es muy alto) y un argumento adicional: el nombre del archivo que intenta ejecutarse y cuya primera línea es ese <em>shebang</em>.</p></li>
                                        <li><p>La idea es que un archivo de código de un lenguaje interpretado especifique así en su primera línea cuál es el interpretador que debe usarse para ejecutarlo. Así, las siguientes líneas contiguas podrían ser el contenido de un archivo que se ejecute con este mecanismo usando el <em>shell</em> Bash:</p></li>
                                        <li><blockquote>
<pre><code><![CDATA[
#!/bin/bash
for i in {1..10}
do
        echo &quot;Hola $i&quot;
done
]]></code></pre>
                                        </blockquote></li>
                                        <li><p>Esto funciona porque el ejecutable en <code>/bin/bash</code> recibe como parámetro el camino al archivo cuyo contenido se mostró, y cuando <code>bash</code> recibe el nombre de un archivo, lo que hace es ejecutar su contenido como <em>shell script</em>; es decir, lo interpreta.</p></li>
                                        <li><p>Para Python y Ruby el asunto es igual: los ejecutables <code>python</code> y <code>ruby</code> ejecutan el código contenido en el archivo cuyo nombre les sea pasado como parámetro. El único problema es saber dónde están instalados esos ejecutables; la solución es usar a otro programa que determina esto automáticamente:</p></li>
                                        <li><blockquote>
<pre><code><![CDATA[
#!/usr/bin/env ruby
]]></code></pre>
                                        </blockquote></li>
                                        <li><p>para Ruby, o</p></li>
                                        <li><blockquote>
<pre><code><![CDATA[
#!/usr/bin/env python
]]></code></pre>
                                        </blockquote></li>
                                        <li><p>para Python, y sí, también sirve</p></li>
                                        <li><blockquote>
<pre><code><![CDATA[
#!/usr/bin/env bash
]]></code></pre>
                                        </blockquote></li>
                                        <li><p>para Bash. Usando este mecanismo, el propio ejecutable es capaz de ejecutarse directamente como lo pide el enunciado sin necesidad de un <em>script</em> aparte.</p></li>
                                        <li><p>El único problema que queda es Java (como de costumbre). Los ejecutables de Java en la implantación recomendada no son texto plano (así que no se puede usar el mecanismo de <em>shebang</em>) ni código compilado para la máquina real (así que tampoco pueden ejecutarse directamente), sino código compilado para la máquina virtual de Java. Para ejecutarlos habría que escribir algo como <code>java LexAsgard</code>. Este es el único caso para el cual haría falta hacer un <em>script</em>, pero sería trivial:</p></li>
                                        <li><blockquote>
<pre><code><![CDATA[
#!/bin/sh
java LexAsgard
]]></code></pre>
                                        </blockquote></li>
                                        <li><p>Otro detalle importante es que la entrada se lee por la entrada estándar sin ningún tipo de <em>prompt</em>. La lectura “línea por línea” es algo que solo tiene sentido hacer cuando se está haciendo un interpretador de línea de comando, como un <em>shell</em> o un ambiente <em>REPL</em> (<em><strong>r</strong>ead, <strong>e</strong>val, <strong>p</strong>rint <strong>l</strong>oop</em>) para algún lenguaje. Este lenguaje no funcionará con un <em>REPL</em> y el espacio en blanco no tiene relevancia sintáctica, así que <strong>el concepto de <em>línea</em> es totalmente irrelevante</strong>. Su programa debería leer el archivo de entrada completo, o ir leyéndolo mientras vaya siendo necesario procesar más entrada, pero las fronteras entre una línea y otra son irrelevantes y se ignorarán como se ignora cualquier otro espacio en blanco según la especificación del lenguaje.</p></li>
                                        <li><p>Como el enunciado especifica el formato de salida de la primera etapa del proyecto, no deberían producir mensajes de ningún tipo pidiendo al usuario que escriba el código a analizar, ni nada por el estilo.</p></li>
                                </ol>
                        </li>
                        <li id="pregunta2">
                                <h3>Tipos de datos para los <em>token</em>s</h3>
                                <h4>Pregunta</h4>
                                <blockquote>
                                        <ol>
                                                <li><p>La otra duda que tenia es sobre esta parte del enunciado "Es importante notar que cada token producido por su analizador lexicográ co debe corresponder a un tipo de datos y no simplemente ser impreso a la salida estándar."; no entendi que quieren decir con eso, significa que tenemos que guardar los tokens en alguna parte después de que el analizador termine?</p></li>
                                        </ol>
                                </blockquote>
                                <h4>Respuesta</h4>
                                <ol>
                                        <li><p>La idea es que no solamente deben imprimir por pantalla en el formato especificado cuáles son los <em>token</em>s encontrados (si no hubo errores), sino que deberían tener a los <em>token</em>s representados como entes independientes en su programa según sea adecuado en su lenguaje. En Haskell deben hacer un tipo abstracto de datos (con <code>data</code>); en C++ lo ideal sería una tupla o un registro con un valor de un <code>enum class</code> y el dato contenido si lo hay; en Ruby querrán definir una clase para cada <em>token</em> (y pueden aprovechar técnicas de metaprogramación para hacerlo con mucha facilidad); en el caso de Python con PLY, que no trabaja con tipos distintos para cada <em>token</em> sino con reflexión y variables con nombres especiales, ese requerimiento tiene poco sentido, pero podrían arreglárselas con una simple tupla.</p></li>
                                        <li><p>El problema con ese requerimiento del enunciado es que no puede ser demasiado específico por la diversidad de lenguajes que pueden usar para el proyecto. En general la idea es que no se limiten a simplemente imprimir los <em>token</em>s cuando los encuentren, sino que tengan alguna estructura de datos independiente que los represente y almacene.</p></li>
                                </ol>
                        </li>
                </ol>
                <hr/>
                <p>
                        <a href="http://validator.w3.org/check?uri=referer">
                                <img src="http://www.w3.org/Icons/valid-xhtml11" alt="Valid XHTML 1.1" height="31" width="88"/>
                        </a>
                </p>
                <p>Y lo escribí a mano en Vim sin más referencia que el <code>DOCTYPE</code> y el <code>xmlns</code>.</p>
                <p>Like a boss.</p>
        </body>
</html>
