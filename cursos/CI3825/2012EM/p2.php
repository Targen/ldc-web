<?php
        // Content negotiation? #merwebo.
        // XHTML ftw; una solución decente requeriría una configuración de Apache decente.
        header('Content-Type:application/xhtml+xml;charset=utf-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
        <head>
                <meta http-equiv="Content-type" content="application/xhtml+xml;charset=utf-8"/>
                <title>Notas de Manuel Gómez sobre CI3825 — Consultas del primer proyecto</title>
                <style type="text/css">p { text-align:justify; }</style>
        </head>

        <body>
                <h1><a href="../../../..">Manuel Gómez</a> — <a href="../../..">Cursos</a> — <a href="../..">CI3825</a> — <a href="..">Enero–Marzo de 2012</a></h1>
                <hr/>
                <h2>2012‒02‒07 (semana 5): Consultas del primer proyecto</h2>
                <p>Acá les dejo las preguntas y respuestas de varias consultas que me han hecho estudiantes del curso sobre el primer proyecto.  Espero que les sirvan.</p>
                <ol>
                        <li><a href="#p1">Usuarios enviados a colas de atracciones cerradas</a></li>
                        <li><a href="#p2">Orden de los trabajadores                        </a></li>
                        <li><a href="#p3">Momento preciso para la comunicación             </a></li>
                        <li><a href="#p4">Procesos vs. hilos                               </a></li>
                </ol>
                <ol>
                        <li id="p1">
                                <h3>Usuarios enviados a colas de atracciones cerradas</h3>
                                <h4>Pregunta</h4>
                                <blockquote>
                                        <p>Sabes que es un anillo de procesos.. pero que pasa si un proceso es muy rapido y termina todo antes que los demas??</p>
                                </blockquote>
                                <h4>Respuesta</h4>
                                <p>Si un trabajador termina, es porque ya ejecutó todas las iteraciones que debía ejecutar.  En ese caso, cualquier usuario que salga del trabajador anterior hacia el que terminó se quedaría en la cola y nunca podría montarse en la atracción.  Sin embargo, usuarios en esta situación no se consideran como usuarios encolados en la atracción que terminó: la §Enunciado.4.5 de <a href="https://ldc.usb.ve/~yudith/docencia/ci-3825/Proy2">https://ldc.usb.ve/~yudith/docencia/ci-3825/Proy2</a> dice:</p>
                                <blockquote>
                                        <p>[…] Al finalizar todas las iteraciones, la atracción se cierra, e informa al proceso/hilo principal cuántos usuarios quedaron en la cola</p>
                                </blockquote>

                                <p>El número de usuarios que han quedado en la cola de una atracción es reportado inmediatamente por su trabajador al terminar.  Si luego de cerrar una atracción hay usuarios salientes de la atracción anterior, éstos no se reportarían como encolados en ella (y tampoco pueden saltarse una atracción y quedar en la siguiente, así que se perderían).</p>
                        </li>
                        <li id="p2">
                                <h3>Orden de los trabajadores</h3>
                                <h4>Pregunta</h4>
                                <blockquote>
                                        <p>Como se cual es el orden de mis procesos?? y en que orden debo ir haciendo el pase de parámetros entre ellos??</p>
                                </blockquote>
                                <h4>Respuesta</h4>
                                <p>En efecto, pareciera que el enunciado no dice ni explícita ni implícitamente el orden de las atracciones, aunque me parece que la intención habría sido que el sucesor de la atracción especificada por cada línea sea aquella que es especificada por la línea siguiente (como aparecen en el archivo de entrada), excepto en el caso de la última atracción (especificada en la última línea, que no tiene línea siguiente) cuyo sucesor sería la atracción especificada en la primera línea del archivo de entrada que especifica una atracción (que sería la segunda línea del archivo de entrada, porque la primera especifica otras cosas).</p>
                        </li>
                        <li id="p3">
                                <h3>Momento preciso para la comunicación</h3>
                                <h4>Pregunta</h4>
                                <blockquote>
                                        <p>Sabs q x ejemplo la atraccionA  tarde 1 minuto y la atraccionB tarda 2, cuando le pasa la informacion?? cuando termina la A?? o cuando se desocupa la B?? pq serian valores diferentes..</p>
                                </blockquote>
                                <h4>Respuesta</h4>
                                <p>Sobre el comportamiento de los trabajadores, la §Enunciado.4.4 de <a href="https://ldc.usb.ve/~yudith/docencia/ci-3825/Proy2">https://ldc.usb.ve/~yudith/docencia/ci-3825/Proy2</a> dice:</p>
                                <blockquote>
                                        <p>al despertar, enviar los usuarios que estaban montados a la siguiente atracción en el anillo, leer cuántos usuarios llegan de la atracción anterior y encolarlos en su cola</p>
                                </blockquote>
                                <p>La información se pasa, entonces, cuando la atracción A termina su tiempo de espera.</p>
                        </li>
                        <li id="p4">
                                <h3>Procesos vs. hilos</h3>
                                <h4>Pregunta</h4>
                                <blockquote>
                                        <p>Tengo demasiadas dudas con los procesos =( jajaja</p>
                                </blockquote>
                                <h4>Respuesta</h4>
                                <p>En realidad no hay mayor diferencia en la idea de lo que pasa en el caso de hilos y el caso de procesos; la diferencia está en los mecanismos de comunicación (pipes no nominales vs. variables compartidas) y sincronización (señales vs. mutexes y joins —y quizás variables de condición, pero creo que no hacen falta—), pero el trabajo que se hace con esos mecanismos es igualito.</p>
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
