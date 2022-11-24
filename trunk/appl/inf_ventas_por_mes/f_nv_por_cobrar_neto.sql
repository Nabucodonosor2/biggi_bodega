create FUNCTION f_nv_por_cobrar_neto(@ve_cod_nota_venta numeric)
RETURNS T_PRECIO
AS
BEGIN
	declare
		@total_neto			T_PRECIO,
		@total_pago_neto	T_PRECIO

	select @total_neto = total_neto
	from nota_venta
	where cod_nota_venta = @ve_cod_nota_venta

	select @total_pago_neto = dbo.f_nv_cobrado_neto(@ve_cod_nota_venta)

	return @total_neto - @total_pago_neto
END
