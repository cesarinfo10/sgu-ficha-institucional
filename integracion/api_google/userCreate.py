# Importar bibliotecas necesarias

from __future__ import print_function

import os.path

print(os.path)

from google.auth.transport.requests import Request
from google.oauth2.credentials import Credentials
from google_auth_oauthlib.flow import InstalledAppFlow
from googleapiclient.discovery import build

# Permiso o alcance (scopes) de la API Directory

SCOPES = ["https://www.googleapis.com/auth/admin.directory.user"]

# Función con los datos de acceso y autenticación a la API Directory


def main():
    creds = None
    if os.path.exists("token.json"):
        creds = Credentials.from_authorized_user_file("token.json", SCOPES)
    if not creds or not creds.valid:
        if creds and creds.expired and creds.refresh_token:
            creds.refresh(Request())
        else:
            flow = InstalledAppFlow.from_client_secrets_file("credentials.json", SCOPES)
            printr(flow)
            creds = flow.run_local_server(port=0)
        with open("token.json", "w") as token:
            token.write(creds.to_json())

# Instancia del cliente de la API Directory

service = build("admin", "directory_v1", credentials=creds)


# Cuerpo de solicitud para creación del usuario

new_user = {
    "name": {
        "familyName": "apellido",
        "givenName": "nombre",
        "displayName": "nombre_usuario",
    },
    "password": "contraseña",
    "primaryEmail": "correo_electronico",
    "orgUnitPath": "unidad",
    "recoveryEmail": "correo_electronico",
    "recoveryPhone": "numero_celular",
}

# Se inserta la solicitud en la API Directory

response = service.users().insert(body=new_user).excute()

# Se verifica la respuesta

if response["status"] == "OK":
    print("El usuario se creo correctamente")

else:
    print("Se produjo un error al crear el usuario")


if __name__ == '__main__':
    main()
