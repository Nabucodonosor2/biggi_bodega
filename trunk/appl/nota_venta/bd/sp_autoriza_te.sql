-------------------- sp_autoriza_te ---------------------------------	
CREATE PROCEDURE [dbo].[sp_autoriza_te](
						@ve_cod_item_nota_venta numeric
						,@ve_cod_usuario_autoriza_te numeric	
						,@ve_motivo_autoriza varchar(100))
AS
BEGIN
		insert into autoriza_te(
					cod_item_nota_venta,
					cod_usuario,
					fecha_autoriza,
					motivo_autoriza)
		values(
					@ve_cod_item_nota_venta,
					@ve_cod_usuario_autoriza_te,
					getdate(),
					@ve_motivo_autoriza)	
END