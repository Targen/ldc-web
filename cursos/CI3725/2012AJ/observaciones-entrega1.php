<?php
        // Content negotiation? #merwebo.
        // XHTML ftw; una solución decente requeriría una configuración de Apache decente.
        header('Content-Type:application/xhtml+xml;charset=utf-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
        <head>
                <meta http-equiv="Content-type" content="application/xhtml+xml;charset=utf-8"/>
                <title>Notas de Manuel Gómez sobre CI3725 — Observaciones sobre una entrega de la primera etapa del proyecto</title>
                <style type="text/css">p { text-align:justify; }</style>
        </head>

        <body>
                <h1><a href="../../..">Manuel Gómez</a> — <a href="../..">Cursos</a> — <a href="..">CI3725</a> — <a href=".">Abril–Julio de 2012</a></h1>
                <hr/>
                <h2>2012‒05‒12 (semana 3): Observaciones sobre una entrega de la primera etapa del proyecto</h2>
                <p>Recibí una entrega un día y unas cuantas horas antes de la fecha y hora límite para la primera etapa del proyecto, y aproveché para hacer algunos comentarios rápidos sobre problemas que tiene esa entrega.  Como los problemas con las entregas suelen ser los mismos, pienso que estos comentarios pueden resultar útiles a los demás.  Claro, no tienen demasiado sentido sin el código de la entrega, pero espero que igual sirvan de algo.</p>
                <ol>
                        <li>
                                <p>La lectura de la entrada la hacen únicamente de un archivo fijo llamado <code>prueba.txt</code>, pero deberían obtener el programa a analizar únicamente de la entrada estándar.</p>
                        </li>

                        <li>
                                <p>La línea <em>shebang</em> o <em>hashbang</em> que tienen está mal:</p>
                                <blockquote>
<pre><code><![CDATA[
#!usr/bin/python
]]></code></pre>
                                </blockquote>
                                <p>debería comenzar con <code>#!</code> y luego tener el camino absoluto a un ejecutable que esté en una posición conocida del sistema. En particular, como no comienza con un <code>/</code> (después del <code>#!</code>), no es un camino absoluto.</p>
                        </li>

                        <li>
                                <p>El archivo a ejecutar debería llamarse precisamente <code>LexAsgard</code>, pero el suyo se llama <code>LexAsgard.py</code>.</p>
                        </li>

                        <li>
                                <p>El formato de salida no es el especificado. En el caso de los <em>tokens</em> que no almacenan datos, no deberían imprimir nada aparte del nombre del tipo de <em>token</em>; por ejemplo, si encuentran una palabra reservada <code>using</code>, deberían imprimir únicamente <code>TkUsing</code> y más nada (para ese <em>token</em>).</p>
                                <p>En el caso de <em>tokens</em> que almacenen algún dato, como los de identificadores y de literales numéricos y de lienzos, deben imprimir entre paréntesis el dato almacenado en el formato especificado por el enunciado de la primera etapa inmediatamente después del nombre del tipo de <em>token</em>; por ejemplo, si encuentran el literal numérico <code>42</code>, deberían imprimir únicamente <code>TkNum(42)</code>, y si encuentran el literal de lienzo <code>&lt;-&gt;</code>, deberían imprimir únicamente <code>TkLienzo(&quot;-&quot;)</code>.</p>
                        </li>

                        <li>
                                <p>Consideren este texto:</p>
                                <blockquote>
<pre><code><![CDATA[
using {- Este programa debería dar errores
lexicográficos, pero -} es reconocido
correctamente! -} using
]]></code></pre>
                                </blockquote>
                                <p>Los comentarios en AsGArD no pueden contener a la secuencia <code>-}</code>, por lo que ese texto no es un programa válido en AsGArD. En particular, un <em>lexer</em> para AsGArD podría reconocer el primer <code>using</code> como un <code>TkUsing</code>, luego ignorar el espacio, luego ignorar todo hasta el primer fin de comentario (luego de la palabra <code>pero</code>), luego ignorar el espacio antes de la palabra <code>es</code>, luego reconocer tres identificadores (e ignorar dos espacios en medio, claro), y luego darse cuenta de que hay un error: el signo de exclamación es un caracter inválido, y también lo será el segundo caracter <code>}</code>.</p>
                                <p>Su expresión regular para los comentarios está escrita de forma tal que se toma como comentario desde el primer <code>{-</code> hasta el último <code>-}</code> de un programa. Sin embargo, los comentarios deberían cerrarse con el fin de comentario más cercano, no con el más lejano.</p>
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
