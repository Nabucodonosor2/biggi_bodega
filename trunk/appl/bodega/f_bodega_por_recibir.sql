--------------------  f_bodega_por_recibir  ----------------
alter FUNCTION f_bodega_por_recibir(@ve_cod_producto varchar(20))
RETURNS numeric(10,2)
AS
BEGIN
	declare
		@por_recibir T_CANTIDAD

	select @por_recibir = isnull(sum(dbo.f_sol_por_llegar(cod_solicitud_compra)), 0)
	from solicitud_compra
	where cod_estado_solicitud_compra = 2 -- confirmado
	  and cod_producto = @ve_cod_producto

RETURN @por_recibir

END


