/* Look ma, no assignment! */

#include <stdio.h>

void contador(int n) {
  printf(
    "\n"
    "Contador!\n"
    "Opciones:\n"
    "0) Salir\n"
    "1) Borrar\n"
    "2) Iniciar\n"
    "3) Aumentar\n"
    "4) Imprimir\n"
  );

  L: switch (getchar()) {
    case '\n':
      goto L;

    case -1:
    case '0':
      puts("Bueno, chao.");
      break;

    case '1':
      puts("Borraré el contador.");
      contador(-1);
      break;

    case '2':
      puts("Inicializaré el contador a cero.");
      contador(0);
      break;

    case '3':
      puts("Incrementaré el contador.");
      contador(n + 1);
      break;

    case '4':
      if (n == -1) {
        puts("El contador no está inicializado.");
      } else {
        printf("El contador vale %d.\n", n);
      }

      puts("Volveré al menú sin cambiar el valor del contador.");
      contador(n);
      break;

    default:
      puts("Esa opción no existe.  Intente otra vez.");
      contador(n);
      break;
  }
}

int main() {
  puts("Comenzaré el programa con un contador sin inicializar.");
  contador(-1);
}
