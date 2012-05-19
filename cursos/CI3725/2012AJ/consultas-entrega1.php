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
                <h2>2012‒05‒04 (semana 2): Consultas de la primera entrega del proyecto</h2>
                <p>Acá les dejo las preguntas y respuestas de varias consultas que me han hecho estudiantes del curso sobre el segundo proyecto.  Espero que les sirvan.</p>
                <ol>
                        <li><a href="#pregunta1" >Lectura de entrada y mecanismo de ejecución                                             </a></li>
                        <li><a href="#pregunta2" >Tipos de datos para los <em>token</em>s                                                 </a></li>
                        <li><a href="#pregunta3" >Comentarios incompletos y errores léxicos                                               </a></li>
                        <li><a href="#pregunta4" ><em>Token</em> para <code>of type</code>: <code>TkOfType</code> vs. <code>TkIdent</code></a></li>
                        <li><a href="#pregunta5" >Problemas de <code>of type</code> específicos a Python+PLY                              </a></li>
                        <li><a href="#pregunta6" >Lectura por entrada estándar                                                            </a></li>
                        <li><a href="#pregunta7" >Punto de entrada en lenguajes dinámicos                                                 </a></li>
                        <li><a href="#pregunta8" >Informe                                                                                 </a></li>
                        <li><a href="#pregunta9" >Espacio en blanco y delimitadores de literales de lienzo en el formato de salida        </a></li>
                        <li><a href="#pregunta10">Transiciones λ en autómatas finitos determinísticos                                     </a></li>
                        <li><a href="#pregunta11">Identificadores en la revisión teórico‐práctica                                         </a></li>
                        <li><a href="#pregunta12">Estados finales en el autómata de la unión de varios lenguajes: ¿uno o muchos?          </a></li>
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
                        <li id="pregunta3">
                                <h3>Comentarios incompletos y errores léxicos</h3>
                                <h4>Pregunta</h4>
                                <blockquote>
                                        <ol>
                                                <li><p>Sabemos que los comentarios son de la siguiente forma: "{- comentario -}", pero que sucede si No se cierra "-}", ? Es decir: "{- comentario ... Fin de archivo". Es tomado "{-" como caracter invalido o desde "{- hasta el fin d archivo" seria un comentario?</p></li>
                                        </ol>
                                </blockquote>
                                <h4>Respuesta</h4>
                                <ol>
                                        <li><p>La sección 5 de la especificación del lenguaje dice</p></li>
                                        <li><blockquote>
                                                <p>En AsGArD es posible comentar secciones completas del programa, para que sean ignorados [sic] por el interpretador del lenguaje, <strong>encerrando</strong> dicho código entre los símbolos “<code>{-</code>” y “<code>-}</code>”.</p>
                                        </blockquote></li>
                                        <li><p>(Énfasis mío.)  Según esta definición, un inicio de comentario que no tenga en alguna parte del archivo un fin de comentario que le corresponda no definiría un comentario, porque no hay texto <em>encerrado</em> entre esas secuencias.  En ese caso, el <code>{</code> sería un caracter inválido, el <code>-</code> sería el <em>token</em> correspondiente a la resta o el inverso aritmético, y todo lo demás se procesaría como código del programa.  Claro, como ya habrían encontrado un caracter ilegal, así que no les interesaría producir esos <em>tokens</em> sino encontrar más errores e imprimirlos todos al final.</p></li>
                                </ol>
                        </li>
                        <li id="pregunta4">
                                <h3><em>Token</em> para <code>of type</code>: <code>TkOfType</code> vs. <code>TkIdent</code></h3>
                                <h4>Pregunta</h4>
                                <blockquote>
                                        <ol>
                                                <li><p>2- En cuanto a la palabra reservada of type. Of junto a type forman una palabra reservada? O por separado tambien lo son? Es decir, of (sola) es palabra reservada y type (sola) tambien es palabra reservada? Si esto no fuera asi, entonces existe la posibilidad de que of sea un identificador, al igual que type.</p></li>
                                        </ol>
                                </blockquote>
                                <h4>Respuesta</h4>
                                <ol>
                                        <li><p>El enunciado de la primera etapa del proyecto especifica que las palabras <code>of</code> y <code>type</code> juntas constituyen un único <em>token</em> en conjunto. El problema es que no se especifica con mucha claridad lo que significa que estén juntas. Consulté este asunto con los autores de la especificación (Ricardo y Carlos, los profesores de la teoría) y la aclaratoria fue que deben estar separadas por espacios en blanco de cualquier tipo y en cualquier cantidad. Los espacios, tabuladores y fines de línea regulares son espacios en blanco, y también lo son los comentarios. Por ejemplo, este código debería producir 7 <em>tokens</em> del tipo <code>TkOfType</code>:</p></li>
                                        <li><blockquote>
<pre><code><![CDATA[
of type         {- Con un espacio normal        -}
of	type    {- Con un tabulador             -}
of
type            {- Con un fin de línea          -}
of         
       
   		          
         		   	
type            {- Con muchos de los anteriores -}
of{- Con un comentario -}type
of{-Con-}{- varios -}{- comentarios -}type
of    {- Con

      	        un-}	   	   {-
     poco de
todo-}type
]]></code></pre>
                                        </blockquote></li>
                                        <li><p>Este código, en cambio, solo debería producir <em>tokens</em> del tipo <code>TkIdent</code>:</p></li>
                                        <li><blockquote>
<pre><code><![CDATA[
of type1
oftype
of typeof type
Of type
of typealgo
algoof type
type
of
]]></code></pre>
                                        </blockquote></li>
                                </ol>
                        </li>
                        <li id="pregunta5">
                                <h3>Problemas de <code>of type</code> específicos a Python+PLY</h3>
                                <h4>Pregunta</h4>
                                <blockquote>
                                        <ol>
                                                <li><p>Estoy utilizando python, y estoy teniendo problemas con el token of type, porque dado que uno de los caracteres ignorados es el espacio en blanco, primero reconoce a of como identificador, y luego type como identificador. He hecho algunos "trucos" con la finalidad de que reconozca el token completo, pero estos "trucos" me generan otros problemas cuando aparece of type o alguna variante de ella, algunos de ellos (son de soluciones/trucos distintos):</p></li>
                                                <li><ol>
                                                        <li><p>Si codigo de entrada tiene oftype lo reconoce como token de of type.</p></li>
                                                        <li><p>si aparece of type, muestra el token del of type, pero ademas reconoce a of como identificador..</p></li>
                                                        <li><p>y algunos otros...</p></li>
                                                </ol></li>
                                        </ol>
                                </blockquote>
                                <h4>Respuesta</h4>
                                <ol>
                                        <li><p>Una de las cualidades peculiares y poco intuitivas de PLY es que el procesamiento de las expresiones regulares que se le especifiquen se realiza con una opción de la implantación de expresiones regulares de Python que hace que se ignoren los espacios en la expresión. El propósito de ese modo de operación es que se puedan escribir expresiones regulares largas y complejas separando sus partes con espacios, incluso en múltiples líneas y con comentarios, sin que esos espacios y comentarios alteren sus significados. A esto se debe que el <em>string</em> de Python <code>'of type'</code> reconozca las dos palabras juntas y no reconozca a dos palabras separadas por un espacio: PLY no ve ese espacio. Una solución sencilla es escribir algo como <code>'of[ ]type'</code>, pero como también deben poder ser reconocidos <em>tokens</em> <code>TkOfType</code> con muchos espacios en blanco en medio, incluyendo comentarios, la expresión regular sería bastante más compleja que eso. Pueden leer detalles sobre ese modo de operación en <a href="http://docs.python.org/library/re.html#re.X"><code class="url">http://docs.python.org/library/re.html#re.X</code></a>.</p></li>
                                        <li><p>También deben tomar en cuenta la forma en la que PLY decide cuál <em>token</em> emitir cuando hay más de una posibilidad porque las expresiones regulares de dos o más <em>tokens</em> describan lenguajes que no son disjuntos. En particular, <code>of type</code> puede reconocerse como un <em>token</em> <code>TkOfType</code>, pero también podría reconocerse un <em>token</em> <code>TkOf</code>. La documentación de PLY (en <a href="https://www.dabeaz.com/ply/ply.html"><code class="url">https://www.dabeaz.com/ply/ply.html</code></a>) especifica los mecanismos que tiene (su versión actual) para decidir entre las posibilidades situaciones de ambigüedad como esa. En general, se emite el <em>token</em> correspondiente a la primera expresión regular cuyo reconocimiento sobre el inicio de la entrada resulte exitoso, y lo importante es determinar en qué orden se evalúan.</p></li>
                                </ol>
                        </li>
                        <li id="pregunta6">
                                <h3>Lectura por entrada estándar</h3>
                                <h4>Pregunta</h4>
                                <blockquote>
                                        <ol>
                                                <li><p>En el enunciado dice que la entrada será por entrada estándar. Ahora, se introduce el código a analizar y para indicar que terminó, es valido que deba pulsar control D para indicar el eof, o se debe utilizar un archivo y redireccionar?</p></li>
                                        </ol>
                                </blockquote>
                                <h4>Respuesta</h4>
                                <ol>
                                        <li><p>Es equivalente en todos los casos que importan.</p></li>
                                        <li><p>La manera más sencilla de hacer el flujo principal de ejecución de un <em>lexer</em> (y todo lo que le sigue) es leer la totalidad de la entrada a una cadena de caracteres en memoria y trabajar únicamente con eso. No es lo ideal: sería preferible leer en forma incremental y perezosa, consumiendo caracteres de la entrada estándar y ejecutando poco a poco las transiciones del autómata que produce los <em>tokens</em>. Haskell con Alex hace eso de forma prácticamente transparente; en los demás lenguajes probablemente se podría programar, pero no es demasiado práctico, así que es mejor usar la solución costosa pero sencilla.</p></li>
                                        <li><p>La diferencia en el comportamiento de leer poco a poco o leer todo antes de analizar sería que si escriben la entrada directamente en el terminal, podría empezar a reconocerse antes de que terminen si leen poco a poco, mientras que en el caso normal solo se emitirían <em>tokens</em> cuando la entrada estándar indique que no hay más datos por leer. Al final es equivalente, y eso no cambia el resultado en absoluto. No es algo que haga falta tomar en cuenta.</p></li>
                                </ol>
                        </li>
                        <li id="pregunta7">
                                <h3>Punto de entrada en lenguajes dinámicos</h3>
                                <h4>Pregunta</h4>
                                <blockquote>
                                        <ol>
                                                <li><p>[estoy trabajando con python] Se debe contar con un main? En caso de que sea así, es en el donde se lee la entrada, se obtienen los tokens y se imprime? O es indiferente?</p></li>
                                        </ol>
                                </blockquote>
                                <h4>Respuesta</h4>
                                <ol>
                                        <li><p>A diferencia de lo que suele hacerse en la mayoría de los lenguajes compilados, los lenguajes imperativos interpretados y dinámicos como Python, Ruby, Perl y muchos más no definen su punto de entrada en un procedimiento con un nombre especial, como el típico <code>main</code>, sino que el código se ejecuta desde el inicio del archivo a interpretar. A pesar de que es totalmente innecesario para lograr que el código se ejecute, es común encontrar código en Python estructurado así:</p></li>
                                        <li><blockquote>
<pre><code><![CDATA[
def main():
        # Código principal del programa
        ...

if __name__ == '__main__':
        main()
]]></code></pre>
                                        </blockquote></li>
                                        <li><p>Las razones para hacer esto incluyen que pudiera querer usarse ese procedimiento principal en forma recursiva (al darle un nombre se vuelve posible llamarlo explícitamente), y que podría ser deseable evitar que se ejecute el programa si el código no es ejecutado directamente sino que es cargado por otro módulo para reutilizar parte de su funcionalidad. Si un archivo estructurado así se carga directamente por la ejecución de ese archivo de código sobre el interpretador, el cuerpo del condicional se ejecuta y se corre esa función; si la carga de ese código fue indirecta, el cuerpo del condicional no se ejecuta, así que la función <code>main</code> no corre, pero sí se cargan todas las definiciones del archivo (y se ejecutan las demás instrucciones que estén al nivel raíz).</p></li>
                                        <li><p>En resumen: no es necesario, pero pueden hacerlo si por alguna razón les conviene por el diseño de su código.</p></li>
                                </ol>
                        </li>
                        <li id="pregunta8">
                                <h3>Informe</h3>
                                <h4>Pregunta</h4>
                                <blockquote>
                                        <ol>
                                                <li><p>En el enunciado se indica que debemos entregar un breve informe. Esto que debe contener?</p></li>
                                        </ol>
                                </blockquote>
                                <h4>Respuesta</h4>
                                <ol>
                                        <li><p>El informe que deben entregar está compuesto de dos partes:</p></li>
                                        <li><ol>
                                                <li><p>Una explicación breve de las decisiones de diseño que tomaran en su implantación del analizador lexicográfico. La idea no es que digan cosas obvias, como que el proyecto es un <em>lexer</em> o que usaron expresiones regulares, sino cualquier detalle que les parezca relevante sobre su implementación, como las técnicas que usaron para escribir las expresiones regulares más complicadas, o una explicación básica del flujo de ejecución del <em>lexer</em> si lo hacen a mano (para los que usan Ruby), o cualquier cosa de ese estilo.</p>
                                                <p>Es importante destacar la manera en la que se especifica que deben entregar esta parte del proyecto: se habla de un “<em><em>breve</em></em> informe”, y la palabra “breve” está subrayada y resaltada en itálicas. No se compliquen demasiado con eso: la idea es que todo proyecto de software, por simple que sea, implica ciertas decisiones básicas de diseño, de diseño de la implantación, y de implantación (que suena redundante, pero no lo es), y es bueno documentar esas decisiones.</p></li>
                                                <li><p>Las respuestas a los problemas de la <em>revisión teórico-práctica</em>. Esta parte del informe originalmente se entregaría junto con la primera parte y con el código, pero su entrega fue postergada por una semana porque algunos de los problemas requieren que conozcan partes de la teoría del curso que aun no se han cubierto en clase por cambios en el cronograma.</p></li>
                                        </ol></li>
                                        <li><p>Por lo que más quieran, no lo hagan con Office ni nada parecido. Texto plano, Markdown o afines que son casi lo mismo, LaTeX si tienen mucho tiempo libre, o árbol muerto si prefieren lo <em>retro</em>. Al que me envíe un <code>.doc</code> le instalo Windows Millenium Edition.</p></li>
                                </ol>
                        </li>
                        <li id="pregunta9">
                                <h3>Espacio en blanco y delimitadores de literales de lienzo en el formato de salida</h3>
                                <h4>Pregunta</h4>
                                <blockquote>
                                        <ol>
                                                <li><p>Buenos dias, tengo la siguiente duda, cuando imprimimos los tokens, esta impresion debe ser lineal, o debe imprimirse un token en cada linea? O da igual la forma en que decidamos imprimir??</p></li>
                                                <li><p>Ejemplo:</p></li>
                                                <li><blockquote>
<pre><code><![CDATA[
TkUsing TkOfType TkIdent("hola") TkLienzo("</>")
]]></code></pre>
                                                </blockquote></li>
                                                <li><p>o</p></li>
                                                <li><blockquote>
<pre><code><![CDATA[
TkUsing
TkOfType
TkIdent("hola")
TkLienzo("</>")
]]></code></pre>
                                                </blockquote></li>
                                        </ol>
                                </blockquote>
                                <h4>Respuesta</h4>
                                <ol>
                                        <li><p>El enunciado de la primera etapa en efecto especifica ese detalle del formato de salida con poca precisión: se presentan dos ejemplos donde se muestra la representación textual de varios <em>tokens</em> por línea separados por espacios regulares, pero hay varias líneas.</p></li>
                                        <li><p>Presumo que la razón de esa imprecisión es que no es muy importante: separar las representaciones textuales de los <em>tokens</em> con fines de línea o con espacios regulares es equivalente para lo que importa: que la salida pueda reconocerse sin ambigüedad.</p></li>
                                        <li><p>Un detalle que sí está especificado y que deben tomar en cuenta es que la representación textual de los <em>tokens</em> de literales de lienzo no debe incluir a los símbolos <code>&lt;</code> ni <code>&gt;</code> según el punto 5 de la primera lista del enunciado de la primera etapa del proyecto:</p></li>
                                        <li><blockquote>
                                                <ul>
                                                        <li>Los literales lienzos, los cuales serán uno de los siguientes: <code>&lt;empty&gt;</code>, <code>&lt;/&gt;</code>, <code>&lt;\&gt;</code>, <code>&lt;|&gt;</code>, <code>&lt;_&gt;</code>, <code>&lt;-&gt;</code> o <code>&lt; &gt;</code>. Todos estos serán representados por el token <code>TkLienzo</code>, parametrizado por el contenido envuelto entre los sìmbolos <code>&lt;</code> y <code>&gt;</code>. Por ejemplo, el literal de lienzo <code>&lt;/&gt;</code> será representado por <code>TkLienzo("/")</code>.</li>
                                                </ul>
                                        </blockquote></li>
                                </ol>
                        </li>
                        <li id="pregunta10">
                                <h3>Transiciones λ en autómatas finitos determinísticos</h3>
                                <h4>Pregunta</h4>
                                <blockquote>
                                        <ol>
                                                <li><p>En teoría nos dijeron que un autómata finito determinístico NO podía tener lambda transiciones; más al hacer el autómata finito no determinístico correspondiente a la unión de las 3 E.R (pregunta 3), ajuro quedan lambda transiciones para pasar de un estado inicial nuevo a los estados iniciales de cada una de las máquinas, más la lambda transición que tiene la máquina 3 para hacer la clausura de kleene. Al aplicar el algoritmo de hacer ese automáta no determinístico - determinístico, se logra el objetivo de hacerlo determinista, más sigue teniendo lambda transiciones. ¿Está esto mal? ¿La nueva máquina determinista puede seguir teniendo lambda transiciones?</p></li>
                                        </ol>
                                </blockquote>
                                <h4>Respuesta</h4>
                                <ol>
                                        <li><p>Eso estaría mal. Una transición λ en un λ‐NFA</p></li>
                                        <li><blockquote>
                                                <p>M = (Q, F, q₀, δ)</p>
                                        </blockquote></li>
                                        <li><p>se manifiesta cuando existe un estado</p></li>
                                        <li><blockquote>
                                                <p>qᵢ ∈ Q</p>
                                        </blockquote></li>
                                        <li><p>tal que</p></li>
                                        <li><blockquote>
                                                <p>δ(qᵢ, λ) ≠ ∅</p>
                                        </blockquote></li>
                                        <li><p>Esto tiene sentido en un λ‐NFA porque</p></li>
                                        <li><blockquote>
                                                <p>δ : Q × (Σ¹∪{λ}) → 𝒫(Q)</p>
                                        </blockquote></li>
                                        <li><p>La definición de los λ‐NFAs admite la posibilidad de efectuar una transición de la máquina sin consumir símbolos de la entrada porque su función de transición tiene esta forma: recibe un estado y una palabra cualquiera de longitud cero o uno sobre el alfabeto, y produce un conjunto de posibles estados destino. Si la palabra que recibe la función de transición es de tamaño 1, entonces esa transición consume el símbolo de la palabra; si es de tamaño cero, no consume ningún símbolo. Las transiciones λ corresponden a los resultados no nulos de δ con algún estado y con la palabra vacía.</p></li>
                                        <li><p>En cambio, la definición de los DFAs utiliza otra forma para la función de transición:</p></li>
                                        <li><blockquote>
                                                <p>δ : Q × Σ → Q</p>
                                        </blockquote></li>
                                        <li><p>En los DFAs, la función de transición recibe un estado y exactamente un símbolo que es tomado de la entrada. Como tiene que ser exactamente un símbolo, no puede ser una palabra vacía, y no es posible tener transiciones que no consuman exactamente un símbolo, como las transiciones λ que consumen cero símbolos.</p></li>
                                        <li><p>Más intuitivamente, un autómata con transiciones λ no puede ser considerado directamente determinístico por una razón que va más allá de las restricciones de la definición formal. Un autómata determinístico es uno en el cual el estado alcanzado al ejecutar la máquina con cualquier palabra es uno y solo uno al terminar de consumirla. Si un autómata tiene una transición λ entre un estado qᵢ y otro estado qⱼ, entonces siempre que la máquina llegue al estado qᵢ al terminar de consumir su entrada, podría también haber llegado al estado qⱼ. Como habría más de una posibilidad, no habría determinismo.</p></li>
                                        <li><p>Los algoritmos de transformación de λ‐NFA a DFA nunca producen transiciones λ; si lo hicieran, ni siquiera serían consistentes con las definiciones formales de lo que es un λ‐NFA y lo que es un DFA, y tampoco serían consistentes con lo que se entiende por determinismo.</p></li>
                                </ol>
                        </li>
                        <li id="pregunta11">
                                <h3>Identificadores en la revisión teórico‐práctica</h3>
                                <h4>Pregunta</h4>
                                <blockquote>
                                        <ol>
                                                <li><p>Tengo ciertas dudas con la parte teorica-practica de la primera etapa del proyecto.</p></li>
                                                <li><p>Primero, en la 1 la expresion E3 debe reconocer a los identificadores, a que refieren con identificadores? (yeah...Im truly lost...)</p></li>
                                        </ol>
                                </blockquote>
                                <h4>Respuesta</h4>
                                <ol>
                                        <li><p>La expresión regular E3 debería denotar el lenguaje de los identificadores (o nombres) de variables en AsGArD. El tercer párrafo de la sección 0 del documento de especificación del lenguaje AsGArD dice</p></li>
                                        <li><blockquote>
                                                <p>Cada identificador estará formado por una letra seguida de cualquier cantidad de letras y dígitos decimales.</p>
                                        </blockquote></li>
                                        <li><p>Para escribir esa expresión regular (y todas las demás de la revisión teórico-práctica) deberían limitarse a la notación matemática para expresiones regulares que se ha usado en las clases de teoría. Claro, es bastante incómodo describir conjuntos de caracteres en esa notación como los que se usarían en código con cosas como <code>[a-zA-Z]</code>, así que podrían usar una notación abreviada como <code>a + b + ... + z + A + B + ... + Z</code>, o algo por el estilo.</p></li>
                                </ol>
                        </li>
                        <li id="pregunta12">
                                <h3>Estados finales en el autómata de la unión de varios lenguajes: ¿uno o muchos?</h3>
                                <h4>Pregunta</h4>
                                <blockquote>
                                        <ol>
                                                <li><p>Ahora, en la segunda parte cuando refieren a la unión del lenguaje, es unir la maquina M1, con M2 y M3?, o sea tipo un estado inicial que tenga 3 lamdas que conecten las maquinas y lleguen al mismo estado final? De ser así, que me preguntan realmente en la 4? Y a que juegan con nosotros en la 5? (really...)</p></li>
                                        </ol>
                                </blockquote>
                                <h4>Respuesta</h4>
                                <ol>
                                        <li><p>En efecto, cuando se usa la técnica vista en clase para obtener un autómata para la unión de varios lenguajes dados autómatas para cada uno, resulta deseable que el autómata para la unión tenga un solo estado final. Para que esto pase, se agrega un nuevo estado (que se hace final) y se hace que todos los estados finales de las máquinas a unir vayan al nuevo estado final con transiciones λ.</p></li>
                                        <li><p>Sin embargo, condensar los estados finales de la máquina para la unión en un solo estado hace que no se pueda distinguir cuál camino fue tomado para llegar a reconocer la palabra; aunque sí podría conocerse esa información viendo las transiciones completas de la máquina, no sería posible saberlo si lo único que se conoce es a cuál de los estados finales llegó la última transición.</p></li>
                                        <li><p>Un <em>lexer</em> necesita esa información para decidir qué <em>token</em> emitir cuando se reconoce una parte de la entrada. Por lo tanto, cuando construyan el autómata de la unión en este ejercicio, es importante que <strong>no</strong> hagan que solo tenga un único estado final.</p></li>
                                        <li><p>En otros ejercicios es útil hacer que haya un único estado final, porque así se pueden construir máquinas que solo tienen un punto de entrada y un punto de “salida” (o de reconocimiento exitoso), y eso facilita la prueba constructiva de la existencia de un autómata finito que reconoce el mismo lenguaje que cualquier expresión regular. Pero en el caso de estos ejercicios, eso no es deseable.</p></li>
                                        <li><p>Concretamente, la pregunta 4 pide hacer corresponder cada estado final del autómata de la unión con algún tipo de <em>token</em> (que es lo mismo que hacer la correspondencia con alguno de los lenguajes de E1, E2 y E3).</p></li>
                                        <li><p>La pregunta 5 se refiere a la existencia de ciertas palabras que pueden ir a parar a más de uno de esos estados finales cuando se corren sobre el autómata de la unión, así que no se sabría cuál de los <em>tokens</em> emitir. La pregunta 8 tiene que ver con tomar la decisión de cuál de los <em>tokens</em> posibles emitir en esos casos conflictivos o ambiguos.</p></li>
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
