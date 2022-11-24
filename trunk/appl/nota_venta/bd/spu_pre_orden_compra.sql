-------------------- spu_pre_orden_compra ---------------------------------
CREATE PROCEDURE [dbo].[spu_pre_orden_compra](@ve_operacion varchar(20), @ve_cod_pre_orden_compra numeric, @ve_cod_item_nota_venta numeric=NULL, @ve_cod_empresa numeric=NULL, @ve_cantidad T_CANTIDAD=NULL, @ve_precio_compra T_PRECIO=NULL, @ve_cod_producto varchar(30)=NULL, @ve_motivo varchar(100) = null, @ve_cod_usuario numeric=NULL, @ve_genera_compra T_SI_NO=NULL)
AS
BEGIN
	declare @precio_old numeric,
			@cod_pre_orden_compra numeric	

	if (@ve_operacion='INSERT')
	begin	
		insert into pre_orden_compra
		(cod_item_nota_venta, cod_empresa, cantidad, precio_compra, cod_producto, genera_compra)
		values
		(@ve_cod_item_nota_venta, @ve_cod_empresa, @ve_cantidad, @ve_precio_compra, @ve_cod_producto, @ve_genera_compra)

		set @cod_pre_orden_compra = @@identity
		if(@ve_motivo <>'') -- tiene motivo, por lo tanto se modificó el precio
		begin
				SELECT 	@precio_old = dbo.f_prod_get_precio_costo (@ve_cod_producto, @ve_cod_empresa, getdate()) 
				FROM	PRODUCTO_PROVEEDOR PP, EMPRESA E
				WHERE	PP.COD_PRODUCTO = @ve_cod_producto AND
						PP.ELIMINADO = 'N' AND 
						E.COD_EMPRESA = PP.COD_EMPRESA AND						  
						PP.COD_EMPRESA = @ve_cod_empresa 

			insert into modifica_precio_orden_compra 
			(cod_pre_orden_compra, cod_usuario, fecha_modifica, precio_anterior, precio_nuevo, motivo) 
			values 
			(@cod_pre_orden_compra, @ve_cod_usuario, getdate(), @precio_old, @ve_precio_compra, @ve_motivo)	
		end
	end
	else if (@ve_operacion='UPDATE')
	begin

		SELECT 	@precio_old = precio_compra 
		FROM	PRE_ORDEN_COMPRA 
		WHERE	COD_PRE_ORDEN_COMPRA = @ve_cod_pre_orden_compra 

		if(@ve_motivo <>'') -- tiene motivo, por lo tanto se modificó el precio
			insert into modifica_precio_orden_compra 
			(cod_pre_orden_compra, cod_usuario, fecha_modifica, precio_anterior, precio_nuevo, motivo) 
			values 
			(@ve_cod_pre_orden_compra, @ve_cod_usuario, getdate(), @precio_old, @ve_precio_compra, @ve_motivo)

		update pre_orden_compra
		set cod_empresa = @ve_cod_empresa, 
			cantidad = @ve_cantidad,
			precio_compra = @ve_precio_compra,
			cod_producto = @ve_cod_producto,
			genera_compra = @ve_genera_compra
		where cod_pre_orden_compra = @ve_cod_pre_orden_compra
	
		
		
	end	
	else if (@ve_operacion='DELETE')
	begin
		delete pre_orden_compra
		where cod_pre_orden_compra = @ve_cod_pre_orden_compra

		delete modifica_precio_orden_compra
		where cod_pre_orden_compra = @ve_cod_pre_orden_compra

	end
END
go



