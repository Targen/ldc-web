Este ejemplo demuestra el uso del paquete `conduit`[1] para recorrer jerarquías de directorios perezosamente usando muy poco código en Haskell.

[1]: http://hackage.haskell.org/package/conduit



Nota al margen: esta extensión es solo para simplificar el código; hay cosas que usan `Data.Text` en vez de `String`, y con esta extensión puedes escribir un literal de cadena de caracteres normal entre comillas dobles, y eso será un `Data.Text` o un `String` según el contexto — algo así como los literales enteros que son `Num a`, y a veces se vuelven `Int`, `Integer`, o cualquier otro tipo numérico según haga falta (hay una clase `IsString`).

> {-# LANGUAGE OverloadedStrings #-}



Cosas de `Data.Conduit`: dado un `FilePath` `r`, `traverse r` es un `Source` que entra recursivamente en la jerarquía de directorios a partir de `r` y produce cada entrada que encuentra como un `FilePath`:

> import Data.Conduit.Filesystem (traverse)

Y `lazyConsume`, que toma un `Source` cualquiera y devuelve una lista perezosa cuyos elementos serán cada uno de los valores que sean producidos.  Lo genial es que el proceso es perezoso: el `Source` pasado solo produce hasta el último valor de la lista que se evalúe, y no importa si el proceso de generación de valores es estricto o involucra a `IO`, o si es puro.

> import Data.Conduit.Lazy (lazyConsume)

`traverse` toma y genera caminos que son más que solo `String`s, así que hay que usar la versión complicada de `FilePath` y esconder la tradicional y simple:

> import Filesystem.Path (FilePath, hasExtension)
> import Filesystem.Path.CurrentOS (decodeString)
> import Prelude hiding (FilePath)



El ejemplo tiene un `main` que toma un argumento, así que…

> import System.Environment (getArgs)



Esta función aparentemente trivial recibe el camino raíz de la jerarquía de directorios a explorar, y devuelve todos los caminos en una lista que, aunque está dentro de `IO`, es perezosa:

> lazyTraverse :: FilePath -> IO [FilePath]
> lazyTraverse = lazyConsume . traverse False

Luego puedes pasarle la lista que sale de ahí a cualquier código puro que trabaje con listas: en este ejemplo se filtran —perezosamente, claro— los primeros 20 archivos con la extensión `txt`:

> pureStuff :: [FilePath] -> [FilePath]
> pureStuff = take 20 . filter (`hasExtension` "txt")

Y luego un `main` que conecta todo:

> main :: IO ()
> main = do
>   [path] <- getArgs
>   mapM_ print . pureStuff =<< lazyTraverse (decodeString path)

Córrelo en algún directorio con muchos archivos y subdirectorios pero pocos `.txt` para que se note la pereza.
