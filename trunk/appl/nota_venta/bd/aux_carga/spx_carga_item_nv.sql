alter PROCEDURE spx_carga_item_nv
AS
BEGIN  
	insert into ITEM_NOTA_VENTA
		(COD_NOTA_VENTA
		,ORDEN
		,ITEM
		,COD_PRODUCTO
		,NOM_PRODUCTO
		,CANTIDAD
		,PRECIO
		,COD_TIPO_GAS
		,COD_TIPO_ELECTRICIDAD
		,COD_TIPO_TE
		,MOTIVO_TE)
	select numero_nota_venta	--COD_NOTA_VENTA
		,secuencia				--ORDEN
		,item					--ITEM
		,modelo_equipo			--COD_PRODUCTO
		,isnull(descripcion,modelo_equipo) 			--NOM_PRODUCTO, OJO existe un item sin nom_producto!!
		,cantidad				--CANTIDAD
		,precio					--PRECIO
		,null					--COD_TIPO_GAS, se asume NULL por simplicidad
		,null					--COD_TIPO_ELECTRICIDAD, se asume NULL por simplicidad
		,7						--COD_TIPO_TE, se asume OTRO por simplicidad
		,motivo_te				--MOTIVO_TE)
	from aux_item_nota_venta
	where numero_nota_venta < 52000

END
