-------------------- sp_modifica_precio_nota_venta ---------------------------------	
CREATE PROCEDURE sp_modifica_precio_nota_venta(
						@ve_cod_item_nota_venta numeric
						,@ve_cod_producto varchar(30)
						,@ve_cod_usuario_mod_precio numeric
						,@ve_precio T_PRECIO
						,@ve_motivo_mod_precio varchar(100))
AS
declare @precio_old T_PRECIO
BEGIN
		select @precio_old = precio_venta_publico
		from producto
		where cod_producto = @ve_cod_producto

		insert into modifica_precio_nota_venta(
					cod_item_nota_venta,
					cod_usuario,
					fecha_modifica,
					precio_anterior,
					precio_nuevo,
					motivo)
		values(
					@ve_cod_item_nota_venta,
					@ve_cod_usuario_mod_precio,
					getdate(),
					@precio_old,
					@ve_precio,
					@ve_motivo_mod_precio)	
END
go