#!/bin/bash

#Verificar si el puerto 8080 esta ocupado
if lsof -i :8080 > /dev/null 2>&1; then
    echo "El puerto 8080 esta ocupado, desea matar el proceso actual? (y/n)" #default es y si no se ingresa nada
    read -p "Respuesta: " respuesta 
    if [ -z "$respuesta" ]; then
        respuesta="y"
    fi
    if [ "$respuesta" == "y" ]; then
        lsof -i :8080 | awk 'NR>1 {print $2}' | xargs kill
        echo "Proceso matado, levantando nuevo servidor..."
    else
        echo "No se mato el proceso"
        exit 1
    fi
    
fi

#Verificar si el puerto 3306 esta ocupado
if lsof -i :3306 > /dev/null 2>&1; then
    echo "El puerto 3306 esta ocupado, desea matar el proceso actual? (y/n)"
    read -p "Respuesta: " respuesta
    if [ -z "$respuesta" ]; then
        respuesta="y"
    fi
    if [ "$respuesta" == "y" ]; then
        lsof -i :3306 | awk 'NR>1 {print $2}' | xargs kill
        echo "Proceso matado, levantando nuevo servidor..."
    else
        echo "No se mato el proceso"
        exit 1
    fi

fi

#Definir si levantar nuevamente la base de datos
echo "Desea levantar nuevamente la base de datos? (y/n)"
read -p "Respuesta: " respuesta
if [ -z "$respuesta" ]; then
    respuesta="y"
fi
if [ "$respuesta" == "y" ]; then
    echo ""
    echo "--------------------------------"
    echo "Limpiando y creando base de datos..."
    echo "--------------------------------"
    echo ""
    mysql -u root -pasus -h localhost -e "DROP DATABASE IF EXISTS highmind; CREATE DATABASE highmind;"
    mysql -u root -pasus -h localhost highmind < database/migrations/000_schema.sql
    mysql -u root -pasus -h localhost highmind < database/migrations/001_contacto_mensajes.sql
    mysql -u root -pasus -h localhost highmind < database/migrations/004_drop_password.sql 2>/dev/null || true
    echo ""
    echo "--------------------------------"
    echo "Base de datos creada correctamente"
    echo "--------------------------------"
    echo "Desea crear un usuario admin? (y/n)"
    read -p "Respuesta: " respuesta
    if [ -z "$respuesta" ]; then
        respuesta="y"
    fi
    if [ "$respuesta" == "y" ]; then
        echo "--------------------------------"
        echo "Creando usuario admin..."
        echo "--------------------------------"
        mysql -u root -pasus -h localhost highmind < database/migrations/003_usuario_admin.sql
        echo "Usuario admin creado en MySQL (admin@admin.com, es_admin=1)."
        echo "Creá la misma cuenta en Firebase Console (Authentication > Email/Password) para poder iniciar sesión."
    else
        echo "No se creo el usuario admin"
    fi
    
else
    echo "Usando base de datos existente..."
fi

#Ejecutar comando para levantar el servidor php con mensaje y colores
echo -e "\033[32m"
echo "--------------------------------
Levantando servidor php...
--------------------------------"
echo ""
echo "PPPPPPP   HH     HH  PPPPPPP
PP    PP  HH     HH  PP    PP
PP    PP  HH     HH  PP    PP
PPPPPPP   HHHHHHHHH  PPPPPPP
PP        HH     HH  PP
PP        HH     HH  PP
PP        HH     HH  PP"
echo -e "\033[0m"
php -v
php -S localhost:8080 -t public_html public_html/router-dev.php

