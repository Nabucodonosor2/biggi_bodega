CREATE FUNCTION [dbo].[f_nv_pend_te] (@ve_cod_item_nota_venta numeric)
RETURNS varchar(100) 
AS
BEGIN
	declare @vl_count numeric,
			@res varchar(100)

	select @vl_count = count(*) from autoriza_te
	where cod_item_nota_venta = @ve_cod_item_nota_venta

	if (@vl_count = 0)
		set @res = 'style="background-color:#FE2E2E"';
	else
		set @res = '';	

	return @res;
END