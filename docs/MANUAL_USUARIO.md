# Manual de Usuario

**Plataforma Centro de Excelencia Operacional**

## Presentación

El presente documento tiene por objetivo orientar a los usuarios administradores en el uso de la plataforma de campañas del Centro de Excelencia Operacional.

La solución permite crear campañas públicas de difusión o levantamiento de información, administrarlas desde un panel interno y revisar sus resultados desde una interfaz centralizada.

## 1. Objetivo de la plataforma

La plataforma fue diseñada para:

- publicar campañas informativas
- construir formularios públicos
- recopilar respuestas y archivos adjuntos
- compartir campañas mediante enlace y código QR
- consultar resultados y exportarlos

## 2. Funcionalidades incluidas

La plataforma considera las siguientes capacidades:

- acceso de administrador con correo y contraseña
- recuperación de contraseña por correo electrónico
- creación y edición de campañas
- manejo de campañas en estado `Borrador`, `Publicada` y `Cerrada`
- bloques públicos de tipo texto e imagen
- formularios con preguntas de distintos tipos
- apoyo por pregunta mediante texto, imagen o video
- vista pública por token único
- código QR para compartir campañas
- registro y consulta de respuestas
- descarga de archivos adjuntos enviados por usuarios
- exportación de resultados en formato Excel compatible `.xls`

## 3. Tipos de campaña

La plataforma permite trabajar con dos tipos de campaña.

### 3.1 Cuestionario

Campaña orientada a recopilar respuestas de usuarios finales mediante un formulario público.

### 3.2 Informativo

Campaña orientada a difundir contenido sin recibir respuestas.

## 4. Acceso a la plataforma

### 4.1 Ingreso administrador

1. Abra la URL de acceso de la plataforma.
2. Ingrese su correo electrónico y contraseña.
3. Presione `Ingresar`.

### 4.2 Recuperación de contraseña

1. En la pantalla de acceso, seleccione `Recuperar acceso`.
2. Ingrese su correo electrónico.
3. Revise su bandeja de entrada.
4. Abra el enlace recibido.
5. Defina y confirme su nueva contraseña.

## 5. Panel principal

Una vez autenticado, el usuario accede al panel de campañas.

En esta pantalla se visualiza, para cada campaña:

- token único
- estado actual
- tipo de campaña
- título público
- descripción
- accesos a edición, resultados y vista pública

Además, se encuentra disponible el botón `Nueva campaña` para crear una nueva campaña.

## 6. Creación de una campaña

Para crear una nueva campaña:

1. Ingrese al panel.
2. Presione `Nueva campaña`.
3. Complete los datos base.
4. Agregue bloques públicos si corresponde.
5. Agregue preguntas si la campaña es de tipo cuestionario.
6. Presione `Crear campaña`.

## 7. Datos base de la campaña

Los principales campos de configuración son los siguientes.

- `Nombre interno`: referencia administrativa de la campaña
- `Título público`: nombre visible para el usuario final
- `Tipo`: define si la campaña será cuestionario o informativa
- `Estado`: controla visibilidad y recepción de respuestas
- `Inicio`: fecha y hora de inicio, si aplica
- `Cierre`: fecha y hora de cierre, si aplica
- `Descripción pública`: texto introductorio de la campaña
- `Mensaje de confirmación`: mensaje mostrado tras el envío de respuestas

## 8. Estados de campaña

### 8.1 Borrador

- permite trabajar la campaña sin exponerla al público
- el administrador puede visualizarla en modo vista previa
- no está disponible para usuarios externos

### 8.2 Publicada

- queda accesible mediante enlace público y código QR
- permite respuestas cuando la campaña es de tipo cuestionario

### 8.3 Cerrada

- la campaña sigue siendo visible públicamente
- ya no admite nuevas respuestas

## 9. Bloques públicos

Los bloques públicos se utilizan para enriquecer la portada de la campaña y entregar contexto al usuario final.

### 9.1 Tipos de bloque disponibles

- `Texto`
- `Imagen`

### 9.2 Usos sugeridos

Los bloques pueden utilizarse para:

- instrucciones previas
- mensajes institucionales
- explicación del objetivo de la campaña
- apoyo visual

### 9.3 Cómo agregar bloques

1. Ubique la sección `Bloques públicos`.
2. Presione `Añadir bloque`.
3. Seleccione el tipo de bloque.
4. Ingrese título y contenido, o cargue una imagen.
5. Guarde la campaña.

## 10. Preguntas del formulario

Las preguntas se utilizan en campañas de tipo `Cuestionario`.

### 10.1 Tipos de pregunta disponibles

- `Texto libre`
- `Alternativa`
- `Selección múltiple`
- `Verdadero / Falso`
- `Fecha`
- `Correo`
- `Número`
- `Archivo adjunto`

### 10.2 Creación de preguntas

Para agregar una pregunta:

1. Presione `Añadir pregunta`.
2. Ingrese el `Enunciado`.
3. Seleccione el tipo de pregunta.
4. Defina el tipo de apoyo, si aplica.
5. Complete el apoyo correspondiente.
6. Ingrese placeholder cuando corresponda.
7. Marque `Respuesta obligatoria` si aplica.
8. Para preguntas de selección, ingrese una opción por línea.
9. Guarde la campaña.

## 11. Apoyo por pregunta

Cada pregunta puede incluir contenido adicional antes del campo de respuesta.

### 11.1 Tipos de apoyo

- `Texto`
- `Imagen`
- `Video`

### 11.2 Recomendaciones de uso

- use `Texto` para aclaraciones breves
- use `Imagen` con una URL pública válida
- use `Video` con una URL pública, idealmente de YouTube o Vimeo

## 12. Opciones en preguntas de selección

Las preguntas de tipo `Alternativa` y `Selección múltiple` requieren opciones de respuesta.

La carga se realiza con una opción por línea.

Ejemplo:

```text
Presencial
Híbrida
Remota
```

## 13. Vista pública de campaña

Cada campaña dispone de una vista pública única asociada a su token.

Desde la edición de la campaña, el administrador puede acceder mediante el botón `Abrir pública`.

En la vista pública se muestra:

- logo institucional
- título y descripción
- bloques públicos
- código QR para compartir
- formulario de respuesta, cuando corresponda

## 14. Código QR

Cada campaña pública incorpora un código QR para facilitar su difusión.

Este código QR permite:

- compartir la campaña en pantallas o piezas gráficas
- acceder rápidamente desde un dispositivo móvil
- distribuir la campaña mediante impresión o soporte digital

## 15. Respuesta de campañas

Cuando una campaña se encuentra publicada, el usuario final puede:

1. acceder desde el enlace público o desde el QR
2. revisar el contenido de la campaña
3. responder el formulario
4. adjuntar archivos si la pregunta lo permite
5. enviar sus respuestas

Una vez finalizado el proceso, el sistema despliega el mensaje de confirmación configurado por el administrador.

## 16. Archivos adjuntos

Las preguntas de tipo archivo permiten adjuntar documentos o imágenes.

Formatos permitidos:

- PDF
- JPG
- JPEG
- PNG
- DOC
- DOCX
- XLS
- XLSX

Tamaño máximo permitido:

- 10 MB por archivo

## 17. Resultados de campaña

Desde el panel administrador, el acceso `Resultados` permite revisar la información recibida.

En esta sección se visualiza:

- tabla consolidada de respuestas
- detalle por envío
- fecha y hora de envío
- dirección IP, cuando está disponible
- archivos adjuntos enviados

## 18. Exportación a Excel

Desde la pantalla de resultados puede utilizarse el botón `Exportar Excel`.

El archivo generado:

- contiene una fila por envío
- incluye una columna por pregunta
- es compatible con Excel mediante formato `.xls`

## 19. Flujo recomendado de trabajo

Se recomienda operar cada campaña siguiendo este orden:

1. crear la campaña
2. completar los datos base
3. agregar bloques públicos
4. definir las preguntas
5. revisar la vista previa
6. publicar la campaña
7. compartir el enlace o QR
8. monitorear respuestas
9. exportar resultados si es necesario
10. cerrar la campaña cuando finalice

## 20. Buenas prácticas

- trabajar inicialmente en estado `Borrador`
- publicar solo contenido validado
- revisar la vista pública antes de difundir
- redactar títulos y preguntas en forma clara y directa
- validar previamente las URL de imágenes y videos
- mantener el mensaje de confirmación alineado con el objetivo de la campaña

## 21. Problemas frecuentes

### 21.1 La campaña pública no abre

Posibles causas:

- la campaña sigue en `Borrador`
- el enlace fue copiado incompleto
- el token es incorrecto

### 21.2 La campaña no permite responder

Posibles causas:

- la campaña está `Cerrada`
- la campaña aún no está `Publicada`

### 21.3 No llega el correo de recuperación

Se recomienda revisar:

- que el correo exista en el sistema
- la bandeja de spam o correo no deseado
- la configuración SMTP del entorno

### 21.4 No se puede cargar un archivo

Se recomienda verificar:

- que el formato esté dentro de los permitidos
- que el archivo no supere los 10 MB

## 22. Alcance del sistema

La plataforma está orientada a procesos como:

- levantamiento de información
- campañas internas o externas
- formularios de onboarding
- publicaciones informativas
- recolección de documentos
- seguimiento de respuestas desde una interfaz única
