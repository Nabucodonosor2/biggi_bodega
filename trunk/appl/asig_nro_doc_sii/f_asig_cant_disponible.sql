CREATE FUNCTION [dbo].[f_asig_cant_disponible](@ve_cod_asig_nro_doc_sii numeric)
RETURNS numeric
AS
BEGIN
	declare @vl_nro_inicio numeric,
			@vl_nro_termino numeric,
			@vl_nro_inicio_devol numeric,
			@vl_nro_termino_devol numeric,
			@vl_cod_tipo_doc numeric,
			@vl_count_docto numeric,
			@vl_rango_asig numeric,
			@vl_rango_dev numeric,
			@vl_cant_disp numeric
	
	select @vl_nro_inicio = isnull(nro_inicio, 0), 
		   @vl_nro_termino = isnull(nro_termino, 0),
		   @vl_nro_inicio_devol = isnull(nro_inicio_devol, 0),
		   @vl_nro_termino_devol = isnull(nro_termino_devol, 0),
		   @vl_cod_tipo_doc = cod_tipo_doc_sii 	
	from asig_nro_doc_sii
	where cod_asig_nro_doc_sii = @ve_cod_asig_nro_doc_sii
	
	
	
	set @vl_rango_asig = (@vl_nro_termino + 1) - @vl_nro_inicio  -- rango asignado
	
	if (@vl_nro_inicio_devol <> 0)
		set @vl_rango_dev = (@vl_nro_termino_devol + 1) - @vl_nro_inicio_devol  -- rango devuelto
	else
		set @vl_rango_dev = 0
	
	if (@vl_cod_tipo_doc = 1) -- tipo de documento = factura
		select @vl_count_docto = count(*) 
		from factura 
		where nro_factura between @vl_nro_inicio and @vl_nro_termino
	
	else if (@vl_cod_tipo_doc = 2) -- tipo de documento = guia despacho
		select @vl_count_docto = count(*) 
		from guia_despacho 
		where nro_guia_despacho between @vl_nro_inicio and @vl_nro_termino
	
	else if (@vl_cod_tipo_doc = 3) -- tipo de documento = nota crédito
		select @vl_count_docto = count(*) 
		from nota_credito 
		where nro_nota_credito between @vl_nro_inicio and @vl_nro_termino

   /*else if (@vl_cod_tipo_doc = 4) -- tipo de documento = nota débito
		select @vl_count_docto = count(*) 
		from nota_debito
		where nro_nota_debito between @vl_nro_inicio and @vl_nro_termino*/

	--FACTURA EXENTA mu
	else if (@vl_cod_tipo_doc = 5) -- tipo de documento = factura exenta
		select @vl_count_docto = count(*) 
		from factura 
		where nro_factura between @vl_nro_inicio and @vl_nro_termino
		and porc_iva = 0

	--para el caso en que se han devuelto documentos, la 'cantidad disponible' es siempre cero
	if (@vl_rango_dev>0)
		set @vl_cant_disp = 0
	else
		set @vl_cant_disp = @vl_rango_asig - @vl_count_docto - @vl_rango_dev

return @vl_cant_disp;
END
go