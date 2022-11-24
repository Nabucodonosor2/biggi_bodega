---------------------- f_nv_get_saldo -------------------
alter FUNCTION f_nv_get_saldo(@ve_cod_nota_venta numeric)
RETURNS T_PRECIO
AS
BEGIN
	declare @vl_total_con_iva	T_PRECIO,
			@vl_total_asignado_nv	T_PRECIO,
			@vl_total_asignado_fa	T_PRECIO,
			@vl_saldo			T_PRECIO,
			@kl_INGRESO_PAGO_NULA numeric,
			@kl_NV_ANULADA		numeric,
			@kl_estado_doc_sii_impresa numeric, 
			@kl_estado_doc_sii_enviada numeric

	set @kl_INGRESO_PAGO_NULA = 3
	set @kl_NV_ANULADA = 3
	set @kl_estado_doc_sii_impresa = 2
	set @kl_estado_doc_sii_enviada = 3
	
	select @vl_total_con_iva = total_con_iva
	from   nota_venta
	where  cod_nota_venta = @ve_cod_nota_venta and
		cod_estado_nota_venta <> @kl_NV_ANULADA 
	
	select @vl_total_asignado_nv = isnull(sum(monto_asignado),0)
	from ingreso_pago ip, ingreso_pago_factura ipf
	where ipf.cod_doc = @ve_cod_nota_venta and
		ipf.tipo_doc = 'NOTA_VENTA' and
		ip.COD_INGRESO_PAGO = ipf.COD_INGRESO_PAGO and
		ip.COD_ESTADO_INGRESO_PAGO <> @kl_INGRESO_PAGO_NULA 

	select @vl_total_asignado_fa = isnull(sum(monto_asignado),0)
	from ingreso_pago ip, ingreso_pago_factura ipf, factura f
	where f.cod_doc = @ve_cod_nota_venta and
		f.cod_estado_doc_sii in (@kl_estado_doc_sii_impresa, @kl_estado_doc_sii_enviada) and
		ipf.cod_doc = f.cod_factura and
		ipf.tipo_doc = 'FACTURA' and
		ip.COD_INGRESO_PAGO = ipf.COD_INGRESO_PAGO and
		ip.COD_ESTADO_INGRESO_PAGO <> @kl_INGRESO_PAGO_NULA 


	set @vl_saldo = @vl_total_con_iva - @vl_total_asignado_nv - @vl_total_asignado_fa

	return isnull(@vl_saldo,0);
END	
