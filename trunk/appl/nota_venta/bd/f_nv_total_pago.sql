ALTER FUNCTION [dbo].[f_nv_total_pago](@ve_cod_nota_venta numeric)
RETURNS T_PRECIO
AS
BEGIN
	declare @vl_total_asignado_nv	T_PRECIO,
			@vl_total_asignado_fa	T_PRECIO,
			@kl_estado_doc_sii_impresa numeric, 
			@kl_estado_doc_sii_enviada numeric,
			@vl_total_pago T_PRECIO,
			@kl_tipo_doc_pago_nc numeric,
			@cod_doc_ingreso_pago numeric,	
			@total_pago_nc T_PRECIO,
			@kl_ingreso_pago_confirmada numeric,
			@total_pago_ajuste	numeric(14,2)

	set @kl_ingreso_pago_confirmada = 2
	set @kl_estado_doc_sii_impresa = 2
	set @kl_estado_doc_sii_enviada = 3
	set @kl_tipo_doc_pago_nc = 7
	
	select @vl_total_asignado_nv = isnull(sum(monto_asignado),0)
	from ingreso_pago ip, ingreso_pago_factura ipf
	where ipf.cod_doc = @ve_cod_nota_venta and
		ipf.tipo_doc = 'NOTA_VENTA' and
		ip.COD_INGRESO_PAGO = ipf.COD_INGRESO_PAGO and
		ip.COD_ESTADO_INGRESO_PAGO = @kl_ingreso_pago_confirmada

	select @vl_total_asignado_fa = isnull(sum(monto_asignado),0)
	from ingreso_pago ip, ingreso_pago_factura ipf, factura f
	where f.cod_doc = @ve_cod_nota_venta and
		f.cod_estado_doc_sii in (@kl_estado_doc_sii_impresa, @kl_estado_doc_sii_enviada) and
		ipf.cod_doc = f.cod_factura and
		ipf.tipo_doc = 'FACTURA' and
		ip.COD_INGRESO_PAGO = ipf.COD_INGRESO_PAGO and
		ip.COD_ESTADO_INGRESO_PAGO = @kl_ingreso_pago_confirmada 

	-- los pagos con NC que no sean devolucion, no se cuentan como pagos porque para estos casos existe 2 facturacion
	-- y lo que se esta considerando como por pagar se lee de la NV y no de la suma de las FA
	select @total_pago_nc = isnull(sum(mda.monto_doc_asignado), 0)
	from factura f, ingreso_pago_factura ipf, ingreso_pago ip, monto_doc_asignado mda, doc_ingreso_pago dip, nota_credito n
	where f.cod_doc = @ve_cod_nota_venta
	  and (f.tipo_doc = 'NOTA_VENTA' or f.tipo_doc = 'GUIA_DESPACHO')
	  and f.cod_estado_doc_sii in (@kl_estado_doc_sii_impresa, @kl_estado_doc_sii_enviada)
	  and ipf.cod_doc = f.cod_factura
	  and ipf.tipo_doc = 'FACTURA'
	  and ip.cod_ingreso_pago = ipf.cod_ingreso_pago
	  and ip.cod_estado_ingreso_pago = @kl_ingreso_pago_confirmada
	  and mda.cod_ingreso_pago_factura = ipf.cod_ingreso_pago_factura
	  and dip.cod_doc_ingreso_pago = mda.cod_doc_ingreso_pago
	  and dip.cod_tipo_doc_pago = @kl_tipo_doc_pago_nc
	  and n.cod_doc = f.cod_factura
	  and n.nro_nota_credito = dip.nro_doc
	  and n.cod_motivo_nota_credito <> 1	-- DEVOLUCION

	
	--total pago ajustado
	select @total_pago_ajuste = isnull(sum(monto), 0)
	from ajuste_pago_nota_venta
	where cod_nota_venta = @ve_cod_nota_venta

	set @vl_total_pago = @vl_total_asignado_nv + @total_pago_ajuste + @vl_total_asignado_fa - @total_pago_nc

	return isnull(@vl_total_pago,0);
END
go