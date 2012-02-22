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
                        fprintf(stderr, "El formato de esta línea está malo; solo pude hacer %d conversiones.\n", n);
                        exit(EX_DATAERR);
                }

                printf("%d, %d, %s\n", edad, tiempo, nom);
        }

        fclose(fp);
        return 0;
}
