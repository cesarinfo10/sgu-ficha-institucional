
CREATE TABLE sede_ficha (
    id SERIAL PRIMARY KEY,
    nomSede VARCHAR(255) NOT NULL,
    estado INTEGER NOT NULL
);

CREATE TABLE informacion_sedes (
    id SERIAL PRIMARY KEY,
    idSede INTEGER NOT NULL,
    sedeAno INTEGER NOT NULL,
    porc_mujeres DECIMAL(5,2) NULL,
    total INTEGER NOT NULL, 
    cftSede BOOLEAN NULL,
    ipSede BOOLEAN NULL,
    uniSede BOOLEAN NULL,
    CONSTRAINT fk_info_sede_ficha FOREIGN KEY (idSede) REFERENCES sede_ficha(id)
);

CREATE TABLE edi_propio_sedes (
    id SERIAL PRIMARY KEY,
    idSede INTEGER NOT NULL,
    direccion VARCHAR(255) NOT NULL,
    mtEp VARCHAR(50) NULL,
    anoAdquisision INTEGER NOT NULL, 
    cftSede BOOLEAN NULL,
    ipSede BOOLEAN NULL,
    uniSede BOOLEAN NULL,
    CONSTRAINT fk_edi_sede_ficha FOREIGN KEY (idSede) REFERENCES sede_ficha(id)

);


CREATE TABLE edi_arendado_sedes (
    id SERIAL PRIMARY KEY,
    idSede INTEGER NOT NULL,
    propEA VARCHAR(255),
    fecIniEA DATE NOT NULL,
    plazoEA DATE NULL,
    arriendoEA VARCHAR(50),
    metrosCuaEA VARCHAR(50), 
    arriendoCFT BOOLEAN NULL,
    arriendoIP BOOLEAN NULL,
    arriendoUni BOOLEAN NULL,
    CONSTRAINT fk_arrie_sede_ficha FOREIGN KEY (idSede) REFERENCES sede_ficha(id)

);

CREATE TABLE edi_comodato_sedes (
    id SERIAL PRIMARY KEY,
    idSede INTEGER NOT NULL,
    propEC VARCHAR(255),
    fecIniEC DATE NOT NULL,
    plazoEC DATE NULL,
    metrosCuaEC VARCHAR(50), 
    comodatoCFT BOOLEAN NULL,
    comodatoCIP BOOLEAN NULL,
    comodatoUni BOOLEAN NULL,
    CONSTRAINT fk_comodato_sede FOREIGN KEY (idSede) REFERENCES sede_ficha(id)

);
--Evolución de infraestructura total y de otras instituciones del conglomerado por sede.
CREATE TABLE evolucion_conglomerado_sede (
    id SERIAL PRIMARY KEY,
    descripcion TEXT,
    ano INTEGER NOT NULL,
    metrosCuaEC VARCHAR(50)
);

/*Indicadores de infraestructura: M2 totales por estudiantes, volúmenes, títulos,
bases de datos, libros digitales; indicadores de uso/préstamos.*/
CREATE TABLE infraestructura_conglomerado_sede (
    id SERIAL PRIMARY KEY,
    descripcion TEXT,
    ano INTEGER NOT NULL,
    valorCon VARCHAR(50)
);
