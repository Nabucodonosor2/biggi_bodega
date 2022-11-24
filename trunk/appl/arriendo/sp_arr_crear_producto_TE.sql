-------------------- sp_arr_crear_producto_TE ---------------------------------
alter PROCEDURE sp_arr_crear_producto_TE(@ve_cod_item_arriendo numeric)
AS
/*
Para los TE de arriendo es necesario crearlos como equipo
de esta forma se puede manejar inventario de ellos
*/
BEGIN
	declare
		@K_DESCONTINUADO			numeric
		,@vl_cod_producto_TE		varchar(30)
		,@vl_nom_producto			varchar(100)
		,@vl_precio_venta			numeric

	set @K_DESCONTINUADO = 4

	-- los TE los crea como PRODUCTO descontinuado (se llena solo los campos not null)
	set @vl_cod_producto_TE = 'TE' + convert(varchar, @ve_cod_item_arriendo)
	select @vl_nom_producto = nom_producto
			,@vl_precio_venta = precio_venta
	from  item_arriendo
	where cod_item_arriendo = @ve_cod_item_arriendo

	insert into PRODUCTO
		(COD_PRODUCTO
		,NOM_PRODUCTO
		,COD_TIPO_PRODUCTO
		,COD_MARCA
		,LARGO
		,ANCHO
		,ALTO
		,PESO
		,LARGO_EMBALADO
		,ANCHO_EMBALADO
		,ALTO_EMBALADO
		,PESO_EMBALADO
		,FACTOR_VENTA_INTERNO
		,PRECIO_VENTA_INTERNO
		,FACTOR_VENTA_PUBLICO
		,PRECIO_VENTA_PUBLICO
		,USA_ELECTRICIDAD
		,USA_VAPOR
		,USA_AGUA_FRIA
		,USA_AGUA_CALIENTE
		,USA_VENTILACION
		,MANEJA_INVENTARIO
		,ES_DESPACHABLE
		,ES_COMPUESTO
		,PRECIO_LIBRE
	)
	values
		(@vl_cod_producto_TE		--COD_PRODUCTO
		,@vl_nom_producto			--NOM_PRODUCTO
		,@K_DESCONTINUADO			--COD_TIPO_PRODUCTO
		,1							--COD_MARCA=BIGGI
		,0							--LARGO
		,0							--ANCHO
		,0							--ALTO
		,0							--PESO
		,0							--LARGO_EMBALADO
		,0							--ANCHO_EMBALADO
		,0							--ALTO_EMBALADO
		,0							--PESO_EMBALADO
		,0							--FACTOR_VENTA_INTERNO
		,0							--PRECIO_VENTA_INTERNO
		,0							--FACTOR_VENTA_PUBLICO
		,@vl_precio_venta			--PRECIO_VENTA_PUBLICO
		,'N'						--USA_ELECTRICIDAD
		,'N'						--USA_VAPOR
		,'N'						--USA_AGUA_FRIA
		,'N'						--USA_AGUA_CALIENTE
		,'N'						--USA_VENTILACION
		,'S'						--MANEJA_INVENTARIO
		,'N'						--ES_DESPACHABLE
		,'N'						--ES_COMPUESTO
		,'N'						--PRECIO_LIBRE
	)

	update item_arriendo 
	set cod_producto = @vl_cod_producto_TE
	where cod_item_arriendo = @ve_cod_item_arriendo
END