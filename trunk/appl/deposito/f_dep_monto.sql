------------------ f_dep_monto ----------------
create function f_dep_monto(@ve_cod_deposito numeric)
RETURNS T_PRECIO
AS
-- Retorna el monto total de un deposito
BEGIN
	declare @total	T_PRECIO
				
	select	@total = isnull(sum(d.monto_doc), 0)
	from	item_deposito i, doc_ingreso_pago d
	where	i.cod_deposito = @ve_cod_deposito
	  and	d.cod_doc_ingreso_pago = i.cod_doc_ingreso_pago

	return @total
END
