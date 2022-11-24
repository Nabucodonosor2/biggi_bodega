ALTER FUNCTION [dbo].[f_gr_gd_cant_por_recep](@ve_cod_item_guia_despacho numeric)
RETURNS T_CANTIDAD
AS
BEGIN
	declare @vl_por_recep		T_CANTIDAD,
			@vl_cant_gr			T_CANTIDAD,
			@vl_res				T_CANTIDAD
	
	
	select @vl_por_recep = isnull(sum(cantidad), 0) 
	from   item_guia_despacho
	where cod_item_guia_despacho = @ve_cod_item_guia_despacho
	
		select @vl_cant_gr = isnull(sum(cantidad),0)
		from item_guia_recepcion igr, guia_recepcion gr
		where igr.cod_item_doc = @ve_cod_item_guia_despacho and
			igr.tipo_doc = 'ITEM_GUIA_DESPACHO' and
			gr.cod_guia_recepcion = igr.cod_guia_recepcion and
			gr.cod_tipo_guia_recepcion in (1,2) and
			gr.cod_estado_guia_recepcion in (1,2)
			
	set @vl_res = @vl_por_recep - @vl_cant_gr 

	return @vl_res;
END
