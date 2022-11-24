CREATE PROCEDURE [dbo].[sp_nv_por_cerrar]
AS
BEGIN
	declare
	@kl_cod_estado_nota_venta_confirmada	numeric,
	@kl_cod_estado_nota_venta_por_cerrar	numeric,
	@cant_pendiente							numeric

	set @kl_cod_estado_nota_venta_confirmada	= 4  --- estado_nota_venta = confirmada
	set @kl_cod_estado_nota_venta_por_cerrar	= 5  --- estado_nota_venta = por cerrar

	DECLARE C_POR_CERRAR CURSOR FOR 
	select cod_nota_venta from nota_venta 
	where cod_estado_nota_venta in(@kl_cod_estado_nota_venta_confirmada, @kl_cod_estado_nota_venta_por_cerrar)
	order by cod_nota_venta desc

	declare
		@cod_nota_venta		numeric
	OPEN C_POR_CERRAR
	FETCH C_POR_CERRAR INTO @cod_nota_venta
	WHILE @@FETCH_STATUS = 0 BEGIN
		
		exec spu_tipo_pendiente_nota_venta 'LOAD', @cod_nota_venta
		
		select @cant_pendiente = count(*) 
		from tipo_pendiente_nota_venta
		where cod_nota_venta = @cod_nota_venta

		if (@cant_pendiente = 0)
		begin
			update nota_venta
			set cod_estado_nota_venta = @kl_cod_estado_nota_venta_por_cerrar
			where cod_nota_venta = @cod_nota_venta
		end
		else
		begin
			update nota_venta
			set cod_estado_nota_venta = @kl_cod_estado_nota_venta_confirmada
			where cod_nota_venta = @cod_nota_venta
		end	
		FETCH C_POR_CERRAR INTO @cod_nota_venta
	END
	CLOSE C_POR_CERRAR
	DEALLOCATE C_POR_CERRAR
END