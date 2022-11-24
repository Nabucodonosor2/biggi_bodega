-------------------- spu_item_traspaso_bodega ---------------------------------
ALTER PROCEDURE [dbo].[spu_item_traspaso_bodega](@ve_operacion					varchar(20)
										,@ve_cod_item_traspaso_bodega	numeric
										,@ve_cod_traspaso_bodega		numeric=null
										,@ve_orden 						numeric=null
										,@ve_item 						varchar(20)=null
										,@ve_cod_producto 				varchar(30)=null
										,@ve_nom_producto 				varchar(100)=null
										,@ve_cantidad 					T_CANTIDAD=null
										)

AS
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into ITEM_TRASPASO_BODEGA
			(COD_TRASPASO_BODEGA
			,ORDEN
			,ITEM
			,COD_PRODUCTO
			,NOM_PRODUCTO
			,CANTIDAD
			)
		values 
			(@ve_cod_traspaso_bodega
			,@ve_orden
			,@ve_item
			,@ve_cod_producto
			,@ve_nom_producto
			,@ve_cantidad
			)
	end
	if (@ve_operacion='UPDATE') begin
		update ITEM_TRASPASO_BODEGA 
		set ORDEN = @ve_orden
			,ITEM = @ve_item
			,COD_PRODUCTO = @ve_cod_producto
			,NOM_PRODUCTO = @ve_nom_producto
			,CANTIDAD = @ve_cantidad
		where COD_ITEM_TRASPASO_BODEGA = @ve_cod_item_traspaso_bodega
	end
	else if (@ve_operacion='DELETE') begin
		delete ITEM_TRASPASO_BODEGA 
		where COD_ITEM_TRASPASO_BODEGA = @ve_cod_item_traspaso_bodega
	end		
END
go
