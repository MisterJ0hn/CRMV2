# CRM V2 — Documentación de la API

Base URL: `/api`

Todos los endpoints reciben y retornan **JSON**. Los errores incluyen `"success": false` y un campo `"message"` con la descripción.

---

## 1. `POST /api/auth`

Genera un token de acceso temporal.

### Request

```json
{
  "username": "usuario",
  "password": "contraseña"
}
```

| Campo      | Tipo   | Requerido | Descripción           |
|------------|--------|-----------|-----------------------|
| `username` | string | Sí        | Nombre de usuario CRM |
| `password` | string | Sí        | Contraseña del usuario |

### Response exitosa `200`

```json
{
  "token": "a3f8b...",
  "expires_at": "2026-06-12T15:30:00+00:00"
}
```

| Campo        | Tipo   | Descripción                          |
|--------------|--------|--------------------------------------|
| `token`      | string | Token de 64 caracteres hex           |
| `expires_at` | string | Fecha de expiración (ISO 8601, +30 min) |

### Errores

| Código | Descripción                    |
|--------|--------------------------------|
| `401`  | Credenciales inválidas         |

---

## 2. `POST /api/registrar-movimientos`

Recibe los datos completos de una causa judicial desde el sistema PJUD y los persiste en la base de datos. Si la causa (`crm_causa_id`) ya existe, la actualiza; si no, la crea.

### Request

```json
{
  "ok": true,
  "causa": {
    "crm_causa_id": 123,
    "rit": "C-1234-2024",
    "caratulado": "Banco vs Cliente",
    "tribunal_nombre": "1° Juzgado Civil de Santiago",
    "fecha_ingreso": "2024-01-15",
    "estado_proc": "Vigente",
    "etapa": "Discusión",
    "total_movimientos": 5,
    "total_pdfs": 2,
    "estado": "Activo",
    "procedimiento": "Ejecutivo",
    "ubicacion": "Secretaría",
    "doc_ebook": "ebook.pdf",
    "doc_demanda": "demanda.pdf",
    "doc_certificado_envio": "cert.pdf"
  },
  "movimientos": [
    {
      "folio": "1",
      "tiene_pdf": true,
      "etapa": "Discusión",
      "tramite": "Demanda",
      "descripcion": "Presentación demanda ejecutiva",
      "fecha": "2024-01-15",
      "foja": "1",
      "indice": "1",
      "cuaderno_id": null,
      "cuaderno_nombre": null,
      "pdfs": [
        {
          "tipo": "principal",
          "nombre_archivo": "demanda.pdf"
        }
      ],
      "pdfs_solicitud": [
        {
          "fecha": "2024-01-15",
          "mime_type": "application/pdf",
          "nombre_archivo": "solicitud.pdf",
          "referencia": "REF-001"
        }
      ]
    }
  ],
  "escritos": [
    {
      "fecha_ingreso": "2024-01-20",
      "tipo_escrito": "Demanda",
      "solicitante": "Banco",
      "cuaderno_id": null,
      "doc": "escrito.pdf"
    }
  ],
  "exhortos": [
    {
      "rol_origen": "C-1234-2024",
      "tipo_exhorto": "Nacional",
      "rol_destino": "E-001-2024",
      "fecha_orden": "2024-02-01",
      "fecha_ingreso": "2024-02-05",
      "tribunal_destino": "Juzgado Valparaíso",
      "estado_exhorto": "Pendiente",
      "cuaderno_id": null
    }
  ],
  "informacion_receptor": [
    {
      "cuaderno": "Principal",
      "datos_retiro": "Nombre Receptor",
      "fecha_retiro": "2024-03-01",
      "estado": "Entregado"
    }
  ],
  "litigantes": [
    {
      "participante": "Demandante",
      "rut": "12345678-9",
      "persona": "Natural",
      "razon_social": "Banco SA",
      "cuaderno_id": null
    }
  ],
  "notificaciones": [
    {
      "rol": "C-1234-2024",
      "estado_notificacion": "Notificado",
      "tipo_notificacion": "Personal",
      "fecha_tramite": "2024-02-10",
      "tipo_part": "Demandado",
      "nombre": "Juan Pérez",
      "tramite": "Notificación demanda",
      "observacion": "",
      "cuaderno_id": null
    }
  ],
  "anexos": [
    {
      "nombre_archivo": "anexo1.pdf",
      "fecha_documento": "2024-01-15",
      "referencia": "REF-ANX-001"
    }
  ]
}
```

#### Caso `ok: false`

Si el sistema PJUD retorna un error de consulta, se envía `"ok": false`. El CRM marcará la causa como `"NoOk"` sin procesar los demás campos.

```json
{
  "ok": false,
  "causa": {
    "crm_causa_id": 123
  }
}
```

### Estructura raíz del payload

| Campo                 | Tipo    | Requerido | Descripción                                           |
|-----------------------|---------|-----------|-------------------------------------------------------|
| `ok`                  | boolean | No        | `false` marca la causa como NoOk sin procesar datos   |
| `causa`               | object  | Sí        | Datos principales de la causa (ver sub-tabla)         |
| `movimientos`         | array   | No        | Lista de movimientos de la causa                      |
| `escritos`            | array   | No        | Lista de escritos presentados                         |
| `exhortos`            | array   | No        | Lista de exhortos                                     |
| `informacion_receptor`| array   | Sí        | Información del receptor judicial                     |
| `litigantes`          | array   | Sí        | Partes del juicio                                     |
| `notificaciones`      | array   | Sí        | Notificaciones registradas                            |
| `anexos`              | array   | Sí        | Documentos anexos a la causa                          |

#### Sub-objeto `causa`

| Campo                    | Tipo    | Descripción                         |
|--------------------------|---------|-------------------------------------|
| `crm_causa_id`           | integer | ID de la causa en el CRM (PK)       |
| `rit`                    | string  | RIT de la causa                     |
| `caratulado`             | string  | Nombre de las partes                |
| `tribunal_nombre`        | string  | Nombre del tribunal                 |
| `fecha_ingreso`          | string  | Fecha de ingreso al tribunal        |
| `estado_proc`            | string  | Estado procesal                     |
| `etapa`                  | string  | Etapa actual del proceso            |
| `total_movimientos`      | integer | Total de movimientos en PJUD        |
| `total_pdfs`             | integer | Total de PDFs en PJUD               |
| `estado`                 | string  | Estado de administración            |
| `procedimiento`          | string  | Tipo de procedimiento               |
| `ubicacion`              | string  | Ubicación física del expediente     |
| `doc_ebook`              | string  | Nombre del archivo ebook            |
| `doc_demanda`            | string  | Nombre del archivo de demanda       |
| `doc_certificado_envio`  | string  | Nombre del certificado de envío     |

### Response exitosa `200`

```json
{
  "success": true,
  "message": "Datos registrados correctamente",
  "causa_id": 42
}
```

### Errores

| Código | Descripción                                  |
|--------|----------------------------------------------|
| `400`  | `crm_causa_id` no existe en el CRM           |
| `500`  | Payload nulo o error interno al persistir    |

---

## 3. `POST /api/registrar-documentos`

Recibe un documento en Base64 y lo guarda en el filesystem del servidor, asociándolo a una causa. Tras guardar, marca como descargado cualquier registro relacionado al nombre del archivo (ebook, demanda, certificado de envío, anexos, PDFs de movimientos, escritos).

### Request

```json
{
  "crm_causa_id": 123,
  "nombre_archivo": "demanda.pdf",
  "mime_type": "application/pdf",
  "contenido_base64": "JVBERi0xLjQK..."
}
```

| Campo             | Tipo    | Requerido | Descripción                                            |
|-------------------|---------|-----------|--------------------------------------------------------|
| `crm_causa_id`    | integer | Sí        | ID de la causa en el CRM                               |
| `nombre_archivo`  | string  | Sí        | Nombre del archivo incluyendo extensión                |
| `mime_type`       | string  | Sí        | MIME type del archivo (ver tabla de tipos soportados)  |
| `contenido_base64`| string  | Sí        | Contenido del archivo codificado en Base64             |

#### Tipos MIME soportados

| MIME Type                                                              | Extensión |
|------------------------------------------------------------------------|-----------|
| `application/pdf`                                                      | pdf       |
| `application/msword`                                                   | doc       |
| `application/vnd.openxmlformats-officedocument.wordprocessingml.document` | docx  |
| `application/vnd.ms-excel`                                             | xls       |
| `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`   | xlsx      |
| `application/zip`                                                      | zip       |
| `application/json`                                                     | json      |
| `text/plain`                                                           | txt       |
| `text/csv`                                                             | csv       |
| `image/jpeg`                                                           | jpg       |
| `image/png`                                                            | png       |

> Si el archivo ya existe en disco, la operación es idempotente (no reescribe).

### Comportamiento post-guardado

El endpoint busca el `nombre_archivo` en las siguientes entidades y marca `archivo_descargado = 1`:

- `PjudCausa` → `doc_ebook`, `doc_demanda`, `doc_certificado_envio`
- `PjudAnexoCausa` → `doc`
- `PjudAnexoMovimiento` → `nombre_archivo` (en todos los movimientos de la causa)
- `PjudPdf` → `nombre_archivo` (en todos los movimientos de la causa)
- `PjudEscritos` → `doc`

### Response exitosa `200`

```json
{
  "success": true,
  "message": "Datos registrados correctamente",
  "causa_id": 123
}
```

### Errores

| Código | Descripción                                        |
|--------|----------------------------------------------------|
| `500`  | Payload nulo, error al decodificar Base64, o error de escritura en disco |

---

## Notas generales

- Los tokens generados por `/api/auth` **no son requeridos** actualmente en los endpoints `/api/registrar-*` (no hay middleware de autenticación en esas rutas).
- Cada llamada a `/api/registrar-movimientos` y `/api/registrar-documentos` queda registrada en la tabla `api_llamado` con la fecha, el JSON recibido (opcionalmente con los campos Base64 ofuscados según configuración), el estado de éxito y el mensaje de error si lo hubiera.
- La ofuscación de campos Base64 en el log se controla desde `Configuracion#1 → ocultarBase64EnTrasa`.
