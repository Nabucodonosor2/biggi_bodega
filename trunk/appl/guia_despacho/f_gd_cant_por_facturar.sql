CREATE FUNCTION [dbo].[f_gd_cant_por_facturar](
	@ve_cod_item_guia_despacho numeric,
	@ve_filtro varchar(20)=NULL
)
RETURNS T_CANTIDAD 
AS
BEGIN

declare @total				T_CANTIDAD,
		@res				T_CANTIDAD,	
		@total_facturada	T_CANTIDAD

	set @total = 0
	set @total_facturada = 0
	
	select @total = cantidad
	from item_guia_despacho 
	where cod_item_guia_despacho = @ve_cod_item_guia_despacho
		and cantidad > 0
		and precio > 0

	if (@ve_filtro = 'TODO_ESTADO')
		select @total_facturada = isnull(sum(cantidad), 0)
		from item_factura it_f , factura f
		where it_f.tipo_doc = 'ITEM_GUIA_DESPACHO'
		  and it_f.cod_item_doc = @ve_cod_item_guia_despacho
		  and f.cod_factura = it_f.cod_factura 
		  and f.cod_estado_doc_sii in (1, 2, 3)
	else
		select @total_facturada = isnull(sum(cantidad), 0)
		from item_factura it_f , factura f
		where it_f.tipo_doc = 'ITEM_GUIA_DESPACHO'
		  and it_f.cod_item_doc = @ve_cod_item_guia_despacho
		  and f.cod_factura = it_f.cod_factura 
		  and f.cod_estado_doc_sii in (2, 3)
			
	set @res = @total - @total_facturada


	return @res;
END
go