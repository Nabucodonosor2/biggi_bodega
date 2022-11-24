create FUNCTION [dbo].[f_gd_pdte_por_facturar](@ve_cod_guia_despacho numeric)
RETURNS varchar(1)
AS
BEGIN

	declare @vl_por_facturar T_CANTIDAD,
			@vl_res varchar(1)

	select @vl_por_facturar = sum(dbo.f_gd_cant_por_facturar(COD_ITEM_GUIA_DESPACHO, 'TODO_ESTADO'))
		from ITEM_GUIA_DESPACHO
		where COD_GUIA_DESPACHO = @ve_cod_guia_despacho
	
	if (@vl_por_facturar > 0)
		set @vl_res = 'S';
	else
		set @vl_res = 'N';
	
	return @vl_res;
END
