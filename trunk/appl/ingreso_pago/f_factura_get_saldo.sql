CREATE FUNCTION [dbo].[f_factura_get_saldo](@ve_cod_factura numeric)
RETURNS numeric
AS
BEGIN
	declare @vl_total_con_iva	T_PRECIO,
			@vl_total_asignado	T_PRECIO,
			@vl_total_espera	T_PRECIO,
			@kl_INGRESO_PAGO_NULA numeric,
			@kl_estado_doc_sii_impresa numeric, 
			@kl_estado_doc_sii_enviada numeric

	set @kl_INGRESO_PAGO_NULA = 3
	set @kl_estado_doc_sii_impresa = 2
	set @kl_estado_doc_sii_enviada = 3
	
	select @vl_total_con_iva = total_con_iva
	from   factura
	where  cod_factura = @ve_cod_factura and
			cod_estado_doc_sii in (@kl_estado_doc_sii_impresa, @kl_estado_doc_sii_enviada) 
	
	select @vl_total_asignado = isnull(sum(monto_asignado),0)
	from ingreso_pago ip, ingreso_pago_factura ipf
	where ipf.cod_doc = @ve_cod_factura and
		ipf.tipo_doc = 'FACTURA' and
		ip.COD_INGRESO_PAGO = ipf.COD_INGRESO_PAGO and
		ip.COD_ESTADO_INGRESO_PAGO <> @kl_INGRESO_PAGO_NULA 

	set @vl_total_espera = @vl_total_con_iva - @vl_total_asignado 

	return isnull(@vl_total_espera,0);
END
go