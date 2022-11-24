-------------------------- f_sol_por_llegar --------------------
ALTER FUNCTION [dbo].[f_sol_por_llegar](@ve_cod_solicitud_compra numeric)
RETURNS numeric
AS
BEGIN
	declare 
		@vl_cant_solicitado		T_CANTIDAD
		,@vl_cant_item_entrada	T_CANTIDAD

	select @vl_cant_solicitado = cantidad
	from solicitud_compra
	where cod_solicitud_compra = @ve_cod_solicitud_compra

	set @vl_cant_item_entrada = dbo.f_sol_recibido(@ve_cod_solicitud_compra)

	return @vl_cant_solicitado - @vl_cant_item_entrada
END

