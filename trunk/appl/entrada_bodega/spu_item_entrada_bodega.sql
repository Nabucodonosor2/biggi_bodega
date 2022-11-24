-------------------- spu_item_entrada_bodega---------------------------------
ALTER PROCEDURE spu_item_entrada_bodega(@ve_operacion					varchar(20)
										,@ve_cod_item_entrada_bodega	numeric
										,@ve_cod_entrada_bodega			numeric=null
										,@ve_orden 						numeric=null
										,@ve_item 						varchar(20)=null
										,@ve_cod_producto 				varchar(30)=null
										,@ve_nom_producto 				varchar(100)=null
										,@ve_cantidad 					T_CANTIDAD=null
										,@ve_precio 					numeric=null
										,@ve_cod_item_doc				numeric=null
										)
AS
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into ITEM_ENTRADA_BODEGA 
			(COD_ENTRADA_BODEGA
			,ORDEN
			,ITEM
			,COD_PRODUCTO
			,NOM_PRODUCTO
			,CANTIDAD
			,PRECIO
			,COD_ITEM_DOC)
		values 
			(@ve_cod_entrada_bodega		--COD_ENTRADA_BODEGA
			,@ve_orden					--ORDEN
			,@ve_item					--ITEM
			,@ve_cod_producto			--COD_PRODUCTO
			,@ve_nom_producto			--NOM_PRODUCTO
			,@ve_cantidad				--CANTIDAD
			,@ve_precio					--PRECIO
			,@ve_cod_item_doc)
	end
	if (@ve_operacion='UPDATE') begin
		update ITEM_ENTRADA_BODEGA 
		set COD_ENTRADA_BODEGA = @ve_cod_entrada_bodega
			,ORDEN = @ve_orden
			,ITEM = @ve_item
			,COD_PRODUCTO = @ve_cod_producto
			,NOM_PRODUCTO = @ve_nom_producto
			,CANTIDAD = @ve_cantidad
			,PRECIO = @ve_precio
		where cod_item_entrada_bodega = @ve_cod_item_entrada_bodega
	end
	else if (@ve_operacion='DELETE') begin
		delete ITEM_ENTRADA_BODEGA 
		where cod_item_entrada_bodega = @ve_cod_item_entrada_bodega
	end		
END
