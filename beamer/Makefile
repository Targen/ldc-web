# Todos los archivos con estas extensiones serán borrados al hacer `make clean`:
clean-formats = \
        aux     \
        dvi     \
        html    \
        log     \
        nav     \
        out     \
        pdf     \
        snm     \
        toc     \

# Ejecutable de Pandoc:
pandoc = pandoc

# Opciones generales para `pandoc`:
pandoc-opts =                          \
        --incremental                  \
        --table-of-contents            \
        --standalone                   \
        --include-in-header header.tex \

# Comando para compilar una presentación Beamer de Markdown a PDF:
pandoc-beamer-command = $(pandoc) $(pandoc-opts) --write=beamer --variable theme:Warsaw $< -o $@



# Reglas que no tienen que ver con archivos reales:
.PHONY: all clean

all: x.pdf

clean:
	rm -f $(clean-formats:%=./*.%) x.tex



# Reglas patrón para convertir de un formato a otro:
%.pdf: %.md header.tex ; $(pandoc-beamer-command)
%.tex: %.md header.tex ; $(pandoc-beamer-command)
