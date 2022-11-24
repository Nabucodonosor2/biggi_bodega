---------------------------- spdw_nv_ingreso_pago ---------------------------
ALTER procedure [dbo].[spdw_nv_ingreso_pago](@ve_cod_nota_venta numeric)
AS
BEGIN

-- tabla temporal
DECLARE @TEMPO TABLE  
		(COD_INGRESO_PAGO					numeric)

declare @kl_estado_anulada	numeric,
		@kl_estado_doc_sii_impresa	numeric,
		@kl_estado_doc_sii_enviada	numeric
	
	set @kl_estado_anulada = 3
	set @kl_estado_doc_sii_impresa = 2
	set @kl_estado_doc_sii_enviada = 3
	
	--busca los pagos hechos sobre la NV
	insert into @TEMPO
	select ip.cod_ingreso_pago 
	from ingreso_pago ip, ingreso_pago_factura ipf
	where (ipf.cod_doc = @ve_cod_nota_venta and
		ipf.tipo_doc = 'NOTA_VENTA' and
		ip.cod_ingreso_pago = ipf.cod_ingreso_pago and
		ip.cod_estado_ingreso_pago <> @kl_estado_anulada)


	--busca los pagos hechos sobre la factura de la NV
	insert into @TEMPO
	select ip.cod_ingreso_pago
	from ingreso_pago ip, ingreso_pago_factura ipf, factura f
	where (f.cod_doc = @ve_cod_nota_venta and
		f.cod_estado_doc_sii in (@kl_estado_doc_sii_impresa, @kl_estado_doc_sii_enviada) and
		ipf.cod_doc = f.cod_factura and
		ipf.tipo_doc = 'FACTURA' and
		ip.cod_ingreso_pago = ipf.cod_ingreso_pago and
		ip.cod_estado_ingreso_pago <> @kl_estado_anulada)

	select distinct convert(varchar, COD_INGRESO_PAGO)+'|'+convert(varchar, COD_INGRESO_PAGO)  COD_INGRESO_PAGO
	from @TEMPO
END
