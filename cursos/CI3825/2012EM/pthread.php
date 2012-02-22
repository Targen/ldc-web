<?php
        // Content negotiation? #merwebo.
        // No me voy a poner a hacer una versión con los escapes de otra forma solo para gente con browsers chimbos.
        header('Content-Type:application/xhtml+xml;charset=utf-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
        <head>
                <meta http-equiv="Content-type" content="application/xhtml+xml;charset=utf-8"/>
                <title>Notas de Manuel Gómez sobre CI3825 — Ejemplos de sincronización para hilos POSIX</title>
                <style type="text/css">p { text-align:justify; }</style>
        </head>

        <body>
                <h1><a href="../../../..">Manuel Gómez</a> — <a href="../../..">Cursos</a> — <a href="../..">CI3825</a> — <a href="..">Enero–Marzo de 2012</a></h1>
                <hr/>
                <h2>2012‒02‒13 (semana 6): Ejemplos de sincronización para hilos POSIX</h2>
                <p>El fin de semana pasado escribí un par de ejemplos básicos del trabajo con primitivas de sincronización para hilos POSIX; son los que les mostré al inicio de la clase de hoy.  El código está publicado en <a href="https://github.com/Targen/CI3825">el repositorio que tengo en GitHub para cosas del curso</a>, y ahí seguiré publicando modificaciones a ese código si hacen falta, así como cualquier otro ejemplo que vaya saliendo.</p>

                <p>Si no saben trabajar con sistemas de control de versión, insisto en que éste es el momento perfecto en la carrera para familiarizarse con herramientas de programación que les faciliten el trabajo; aprender a usar un sistema de control de versiones distribuido es una de las mejores inversiones que pueden hacer con su tiempo libre para desarrollarse como computistas.  El mejor sistema de control de versiones existente actualmente sin duda alguna es <a href="http://git-scm.com/">Git</a>, y es muy práctico usarlo junto con servicio como el de <a href="https://github.com/">GitHub</a>.</p>

                <p>Mientras tanto, pueden acceder a <code><a href="https://raw.github.com/Targen/CI3825/master/ejemplos-locks-threads/printer.c">printer.c</a></code>, <code><a href="https://raw.github.com/Targen/CI3825/master/ejemplos-locks-threads/prodcons.c">prodcons.c</a></code> y su correspondiente <code><a href="https://raw.github.com/Targen/CI3825/master/ejemplos-locks-threads/Makefile">Makefile</a></code> via Web.  También está el ejemplo sencillo de manejo de señales con <code>sigaction</code> de hoy: <code><a href="https://raw.github.com/Targen/CI3825/master/sigaction/sigaction.c">sigaction.c</a></code> con su <code><a href="https://raw.github.com/Targen/CI3825/master/sigaction/Makefile">Makefile</a></code>.</p>
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
