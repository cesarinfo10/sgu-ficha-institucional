# Importar bibliotecas

from __future__ import print_function

import os.path

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
            creds = flow.run_local_server(port=0)
        with open("token.json", "w") as token:
            token.write(creds.to_json())

# Función para modificar contraseña del usuario.

def update_password(correo_electronico, contaseña):

# Instancia del cliente de la API Directory

service = build("admin", "directory_v1", credentials=creds)

# Cuerpo de solicitud para modificación de contraseña del usuario

user = {
    
    "password": "contraseña",
}

# Se inserta la solicitud en la API Directory

response = service.users().update(body=user).execute()

# Imprime la respuesta de la solicitud

print('Contraseña actualizada con éxito: {}'.format(update_password['primaryEmail']))

# Se llama a la función "update_password" con el parámetro a modificar.

update_password('correo_electrónico', 'contraseña')


if __name__ == '__main__':
    main()
