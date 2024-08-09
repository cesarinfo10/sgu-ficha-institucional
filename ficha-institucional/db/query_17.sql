
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

CREATE TABLE beneficio_pregrado_postgrado (
    id SERIAL PRIMARY KEY,
     /* Beneficio pregrado o postgrado
    donde 1 es pregrado y 2 es postgrado */
    pregrado_posgrado INTEGER NOT NULL,
    /* Beneficio interno o externo
    donde 1 es interno y 2 es externo */
    tipoBeneficio INTEGER NOT NULL,
    descBeneficio TEXT NOT NULL,
    anoBen VARCHAR(255) NOT NULL,
    montoTotal VARCHAR(50) NULL,
    estudiantePorc DECIMAL(5,2) NULL, 
    mujerPors DECIMAL(5,2) NULL
);
