-------------------- f_ingreso_pago_saldo_por_relacionar ---------------------------------
create  FUNCTION f_ingreso_pago_saldo_por_relacionar(@ve_cod_ingreso_pago_factura numeric)
RETURNS T_PRECIO
AS 
BEGIN
	declare
		@saldo_por_relacionar	T_PRECIO,
		@saldo_relacionado		T_PRECIO,
		@monto_asignado			T_PRECIO

	select @monto_asignado = monto_asignado
	from ingreso_pago_factura
	where cod_ingreso_pago_factura = @ve_cod_ingreso_pago_factura

	select @saldo_relacionado = isnull(sum(monto_doc_asignado), 0)
	from monto_doc_asignado
	where cod_ingreso_pago_factura = @ve_cod_ingreso_pago_factura

	set @saldo_por_relacionar = @monto_asignado - @saldo_relacionado
	return @saldo_por_relacionar
end
go


