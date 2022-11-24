
-- Retorna el siguiente nro disponible para este ASIG
CREATE FUNCTION [dbo].[f_asig_get_nro_doc_sii](@ve_cod_asig_nro_doc_sii numeric)
RETURNS numeric
AS
BEGIN
	declare @vl_nro_inicio numeric,
			@vl_nro_termino numeric,
			@cod_tipo_doc numeric,
			@vl_count_docto numeric,
			@vl_rango_asig numeric,
			@vl_nro_doc_sii numeric
	
	select @vl_nro_inicio = isnull(nro_inicio, 0), 
		   @vl_nro_termino = isnull(nro_termino, 0),
		   @cod_tipo_doc = cod_tipo_doc_sii 	
	from asig_nro_doc_sii
	where cod_asig_nro_doc_sii = @ve_cod_asig_nro_doc_sii
	
	set @vl_rango_asig = (@vl_nro_termino + 1) - @vl_nro_inicio
	
	if (@cod_tipo_doc = 1) -- tipo de documento = factura
		select @vl_count_docto = count(*) 
		from factura 
		where nro_factura between @vl_nro_inicio and @vl_nro_termino
	
	else if (@cod_tipo_doc = 2) -- tipo de documento = guia despacho
		select @vl_count_docto = count(*) 
		from guia_despacho 
		where nro_guia_despacho between @vl_nro_inicio and @vl_nro_termino
	
	else if (@cod_tipo_doc = 3) -- tipo de documento = nota crédito
		select @vl_count_docto = count(*) 
		from nota_credito 
		where nro_nota_credito between @vl_nro_inicio and @vl_nro_termino

	else if (@cod_tipo_doc = 4) -- tipo de documento = nota débito
		select @vl_count_docto = count(*) 
		from nota_debito
		where nro_nota_debito between @vl_nro_inicio and @vl_nro_termino
	
	--FACTURA EXENTA mu
	else if (@cod_tipo_doc = 5) --tipo de documento = factura exenta
		select @vl_count_docto = count(*) 
		from factura 
		where nro_factura between @vl_nro_inicio and @vl_nro_termino
		and porc_iva = 0

	set @vl_nro_doc_sii = @vl_nro_inicio + @vl_count_docto
	if (@vl_nro_doc_sii > @vl_nro_termino)
		set @vl_nro_termino = -1

	return @vl_nro_doc_sii;
END	
go
