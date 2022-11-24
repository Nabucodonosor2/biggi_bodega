-------------------- spu_item_ajuste_bodega ---------------------------------
CREATE PROCEDURE [dbo].[spu_item_ajuste_bodega](
					@ve_operacion				varchar(20)
					,@ve_cod_item_ajuste_bodega numeric 	= null		
					,@ve_cod_ajuste_bodega		numeric	 	= null
					,@ve_cod_producto			varchar(30)	= null
					,@ve_cantidad				T_CANTIDAD	= null)
AS
BEGIN
	if (@ve_operacion='INSERT') 
		begin
			insert into item_ajuste_bodega
				(cod_ajuste_bodega
				 ,cod_producto
				 ,cantidad)
			values(@ve_cod_ajuste_bodega
					,@ve_cod_producto
					,@ve_cantidad)
		end
	else if (@ve_operacion='UPDATE')
		begin
			if (@ve_cantidad <> 0) -- si la cantidad es <> de cero hace update, sino borra el ítem 
				update item_ajuste_bodega
				set cod_ajuste_bodega	=	@ve_cod_ajuste_bodega
					,cod_producto		=	@ve_cod_producto
					,cantidad			=	@ve_cantidad

				where cod_item_ajuste_bodega	=	@ve_cod_item_ajuste_bodega
			else
				delete  item_ajuste_bodega
	    		where cod_item_ajuste_bodega  = @ve_cod_item_ajuste_bodega
		end
	else if (@ve_operacion='DELETE') 
		begin
			delete item_ajuste_bodega
			where cod_item_ajuste_bodega = @ve_cod_item_ajuste_bodega
		end
END
go