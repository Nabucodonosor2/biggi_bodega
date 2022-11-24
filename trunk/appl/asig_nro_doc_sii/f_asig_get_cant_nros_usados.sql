-- Retorna la cantidad de nros usados para un tipo de co sii y un rango de nros
CREATE FUNCTION dbo.f_asig_get_cant_nros_usados(@ve_cod_tipo_doc_sii numeric, @ve_nro_inicio numeric, @ve_nro_termino numeric)
RETURNS numeric
AS
BEGIN
	declare @vl_count numeric
	
	if (@ve_cod_tipo_doc_sii = 1) -- tipo de documento = factura
		select @vl_count = count(*)
		from factura 
		where nro_factura between @ve_nro_inicio and @ve_nro_termino
	else if (@ve_cod_tipo_doc_sii = 2) -- tipo de documento = guia despacho
		select @vl_count = count(*)
		from guia_despacho 
		where nro_guia_despacho between @ve_nro_inicio and @ve_nro_termino
	
	else if (@ve_cod_tipo_doc_sii = 3) -- tipo de documento = nota crédito
		select @vl_count = count(*)
		from nota_credito 
		where nro_nota_credito between @ve_nro_inicio and @ve_nro_termino

	else if (@ve_cod_tipo_doc_sii = 4) -- tipo de documento = nota débito
		select @vl_count = count(*)
		from nota_debito
		where nro_nota_debito between @ve_nro_inicio and @ve_nro_termino
	
	--FACTURA EXENTA mu
	else if (@ve_cod_tipo_doc_sii = 5) -- tipo de documento = factura exenta
		select @vl_count = count(*)
		from factura
		where nro_factura between @ve_nro_inicio and @ve_nro_termino
		and porc_iva = 0

	return @vl_count;
END	
go