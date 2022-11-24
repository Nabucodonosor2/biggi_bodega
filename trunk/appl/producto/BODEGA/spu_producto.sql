USE [BODEGA_2809]
GO
/****** Object:  StoredProcedure [dbo].[spu_producto]    Script Date: 09/28/2012 17:28:29 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- ============================================================
--   Nombre:			spu_producto
--   Base de Datos:		BIGGI                                       
-- ============================================================
ALTER PROCEDURE [dbo].[spu_producto]
			(@ve_operacion					varchar(20)
			,@ve_cod_producto				varchar(30) 
			,@ve_nom_producto				varchar(100)
			,@ve_cod_tipo_producto			numeric
			,@ve_cod_marca					numeric
			,@ve_nom_producto_ingles		varchar(100)
			,@ve_cod_familia_producto		numeric
			,@ve_largo						numeric
			,@ve_ancho						numeric
			,@ve_alto						numeric
			,@ve_peso						numeric
			,@ve_largo_embalado				numeric
			,@ve_ancho_embalado				numeric
			,@ve_alto_embalado				numeric
			,@ve_peso_embalado				numeric
			,@ve_factor_venta_interno		T_PORCENTAJE
			,@ve_precio_venta_interno		T_PRECIO
			,@ve_factor_venta_publico		T_PORCENTAJE
			,@ve_precio_venta_publico		T_PRECIO
			,@ve_usa_electricidad			varchar(1)
			,@ve_nro_fases					varchar(1)
			,@ve_consumo_electricidad		numeric(10,2)
			,@ve_rango_temperatura			varchar(100)
			,@ve_voltaje					numeric
			,@ve_frecuencia					numeric
			,@ve_nro_certificado_electrico 	varchar(100)
			,@ve_usa_gas					varchar(1)
			,@ve_potencia					numeric(10,2)
			,@ve_consumo_gas				numeric
			,@ve_nro_certificado_gas 		varchar(100)
			,@ve_usa_vapor					varchar(1)
			,@ve_consumo_vapor				numeric
			,@ve_presion_vapor				numeric
			,@ve_usa_agua_fria				varchar(1)
			,@ve_usa_agua_caliente			varchar(1)
			,@ve_caudal						numeric
			,@ve_presion_agua				numeric
			,@ve_diametro_caneria			varchar(10)
			,@ve_usa_ventilacion			varchar(1)
			,@ve_volumen					numeric
			,@ve_caida_presion				numeric
			,@ve_diametro_ducto				numeric
			,@ve_nro_filtros				numeric
			,@ve_usa_desague				varchar(1)
			,@ve_diametro_desague			varchar(10)
			,@ve_maneja_inventario			varchar(1)
			,@ve_stock_critico				numeric
			,@ve_tiempo_reposicion			numeric
			,@ve_precio_libre				varchar(1)
			,@ve_es_despachable				varchar(1)
			,@ve_sistema_valido			varchar(10)
			,@ve_potencia_kw			numeric(10,2) )
AS
BEGIN
		if (@ve_operacion='UPDATE') 
			begin
				update producto 
				set		nom_producto				= @ve_nom_producto
						,cod_tipo_producto			= @ve_cod_tipo_producto
						,cod_marca					= @ve_cod_marca
						,nom_producto_ingles		= @ve_nom_producto_ingles
						,cod_familia_producto		= @ve_cod_familia_producto
						,largo						= @ve_largo
						,ancho						= @ve_ancho
						,alto						= @ve_alto
						,peso						= @ve_peso
						,largo_embalado				= @ve_largo_embalado
						,ancho_embalado				= @ve_ancho_embalado
						,alto_embalado				= @ve_alto_embalado
						,peso_embalado				= @ve_peso_embalado
						,factor_venta_interno		= @ve_factor_venta_interno
						,precio_venta_interno		= @ve_precio_venta_interno
						,factor_venta_publico		= @ve_factor_venta_publico
						,precio_venta_publico		= @ve_precio_venta_publico
						,usa_electricidad			= @ve_usa_electricidad
						,nro_fases					= @ve_nro_fases
						,consumo_electricidad		= @ve_consumo_electricidad
						,rango_temperatura			= @ve_rango_temperatura
						,voltaje					= @ve_voltaje
						,frecuencia					= @ve_frecuencia
						,nro_certificado_electrico 	= @ve_nro_certificado_electrico
						,usa_gas					= @ve_usa_gas
						,potencia					= @ve_potencia
						,consumo_gas				= @ve_consumo_gas
						,nro_certificado_gas 		= @ve_nro_certificado_gas
						,usa_vapor					= @ve_usa_vapor
						,consumo_vapor				= @ve_consumo_vapor
						,presion_vapor				= @ve_presion_vapor
						,usa_agua_fria				= @ve_usa_agua_fria
						,usa_agua_caliente			= @ve_usa_agua_caliente
						,caudal						= @ve_caudal
						,presion_agua				= @ve_presion_agua
						,diametro_caneria			= @ve_diametro_caneria
						,usa_ventilacion			= @ve_usa_ventilacion
						,volumen					= @ve_volumen
						,caida_presion				= @ve_caida_presion
						,diametro_ducto				= @ve_diametro_ducto
						,nro_filtros				= @ve_nro_filtros
						,usa_desague				= @ve_usa_desague
						,diametro_desague			= @ve_diametro_desague
						--,maneja_inventario			= @ve_maneja_inventario
						,stock_critico				= @ve_stock_critico
						,tiempo_reposicion			= @ve_tiempo_reposicion
						,precio_libre				= @ve_precio_libre						
						,es_despachable				= @ve_es_despachable
						,potencia_kw				= @ve_potencia_kw
				where	cod_producto				= @ve_cod_producto
			end
		else if (@ve_operacion='INSERT') 
			begin
				insert into producto(cod_producto
							,nom_producto
							,cod_tipo_producto
							,cod_marca
							,nom_producto_ingles
							,cod_familia_producto
							,largo
							,ancho
							,alto
							,peso
							,largo_embalado
							,ancho_embalado
							,alto_embalado
							,peso_embalado
							,factor_venta_interno
							,precio_venta_interno
							,factor_venta_publico
							,precio_venta_publico
							,usa_electricidad
							,nro_fases
							,consumo_electricidad
							,rango_temperatura
							,voltaje
							,frecuencia
							,nro_certificado_electrico
							,usa_gas
							,potencia
							,consumo_gas
							,nro_certificado_gas
							,usa_vapor
							,consumo_vapor
							,presion_vapor
							,usa_agua_fria
							,usa_agua_caliente
							,caudal
							,presion_agua
							,diametro_caneria
							,usa_ventilacion
							,volumen
							,caida_presion
							,diametro_ducto
							,nro_filtros
							,usa_desague
							,diametro_desague
							,maneja_inventario
							,stock_critico
							,tiempo_reposicion
							,precio_libre
							,es_despachable
							,sistema_valido
							,potencia_kw)
				values (@ve_cod_producto 
						,@ve_nom_producto
						,@ve_cod_tipo_producto
						,@ve_cod_marca
						,@ve_nom_producto_ingles
						,@ve_cod_familia_producto
						,@ve_largo
						,@ve_ancho
						,@ve_alto
						,@ve_peso
						,@ve_largo_embalado
						,@ve_ancho_embalado
						,@ve_alto_embalado
						,@ve_peso_embalado
						,@ve_factor_venta_interno
						,@ve_precio_venta_interno
						,@ve_factor_venta_publico
						,@ve_precio_venta_publico
						,@ve_usa_electricidad
						,@ve_nro_fases
						,@ve_consumo_electricidad
						,@ve_rango_temperatura
						,@ve_voltaje
						,@ve_frecuencia
						,@ve_nro_certificado_electrico
						,@ve_usa_gas
						,@ve_potencia
						,@ve_consumo_gas
						,@ve_nro_certificado_gas
						,@ve_usa_vapor
						,@ve_consumo_vapor
						,@ve_presion_vapor
						,@ve_usa_agua_fria
						,@ve_usa_agua_caliente
						,@ve_caudal
						,@ve_presion_agua
						,@ve_diametro_caneria
						,@ve_usa_ventilacion
						,@ve_volumen
						,@ve_caida_presion
						,@ve_diametro_ducto
						,@ve_nro_filtros
						,@ve_usa_desague
						,@ve_diametro_desague
						,@ve_maneja_inventario
						,@ve_stock_critico
						,@ve_tiempo_reposicion
						,@ve_precio_libre
						,@ve_es_despachable
						,@ve_sistema_valido
						,@ve_potencia_kw)			
			end 
END