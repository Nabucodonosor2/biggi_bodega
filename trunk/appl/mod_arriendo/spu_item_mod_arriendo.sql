-------------------- spu_item_mod_arriendo---------------------------------
alter PROCEDURE spu_item_mod_arriendo(@ve_operacion				varchar(20)
									,@ve_cod_item_mod_arriendo	numeric
									,@ve_cod_mod_arriendo		numeric=null
									,@ve_orden 					numeric=null
									,@ve_item 					varchar(4)=null
									,@ve_cod_producto 			varchar(30)=null
									,@ve_nom_producto 			varchar(100)=null
									,@ve_cantidad 				T_CANTIDAD=null
									,@ve_precio 				numeric=null
									,@ve_precio_venta 			numeric=null
									,@ve_cod_tipo_te			numeric=null
									,@ve_motivo_te				varchar(100)
									)
AS
BEGIN
	if (@ve_operacion='INSERT')
		insert into ITEM_MOD_ARRIENDO
			(COD_MOD_ARRIENDO       
			,ORDEN              
			,ITEM               
			,COD_PRODUCTO       
			,NOM_PRODUCTO       
			,CANTIDAD           
			,PRECIO             
			,PRECIO_VENTA       
			,COD_TIPO_TE
			,MOTIVO_TE
			)
		values
			(@ve_cod_mod_arriendo	--COD_MOD_ARRIENDO       
			,@ve_orden			--ORDEN              
			,@ve_item			--ITEM               
			,@ve_cod_producto	--COD_PRODUCTO       
			,@ve_nom_producto	--NOM_PRODUCTO       
			,@ve_cantidad		--CANTIDAD           
			,@ve_precio			--PRECIO             
			,@ve_precio_venta	--PRECIO_VENTA       
			,@ve_cod_tipo_te	--COD_TIPO_TE
			,@ve_motivo_te		--MOTIVO_TE
			)
	else if (@ve_operacion='UPDATE')
		update ITEM_MOD_ARRIENDO
		set COD_MOD_ARRIENDO = @ve_cod_mod_arriendo
			,ORDEN = @ve_orden
			,ITEM = @ve_item
			,COD_PRODUCTO = @ve_cod_producto
			,NOM_PRODUCTO = @ve_nom_producto
			,CANTIDAD = @ve_cantidad
			,PRECIO = @ve_precio
			,PRECIO_VENTA = @ve_precio_venta
			,COD_TIPO_TE = @ve_cod_tipo_te
			,MOTIVO_TE = @ve_motivo_te
		where COD_ITEM_MOD_ARRIENDO = @ve_cod_item_mod_arriendo
	else if (@ve_operacion='DELETE')
		delete ITEM_MOD_ARRIENDO 
		where COD_ITEM_MOD_ARRIENDO = @ve_cod_item_mod_arriendo
END
go