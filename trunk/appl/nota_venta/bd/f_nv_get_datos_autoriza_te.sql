CREATE FUNCTION [dbo].[f_nv_get_datos_autoriza_te](@ve_cod_item_nota_venta numeric, @ve_dato_solicitado varchar(20))
RETURNS varchar(100) 
AS
BEGIN
	declare @vl_dato_solicitado varchar(100),
			@res varchar(100),
			@vl_count_autoriza_te numeric

	select @vl_count_autoriza_te = count(*) from autoriza_te
	where cod_item_nota_venta = @ve_cod_item_nota_venta 	
	
	if (@vl_count_autoriza_te = 0)
		set @res = '';
	else
	begin
		if (@ve_dato_solicitado = 'MOTIVO_AUTORIZA')
			select @vl_dato_solicitado = motivo_autoriza from autoriza_te
			where cod_item_nota_venta = @ve_cod_item_nota_venta

		else if (@ve_dato_solicitado = 'FECHA_AUTORIZA')
			select @vl_dato_solicitado = convert(varchar(10), fecha_autoriza, 103) +'	' + convert(varchar(10), fecha_autoriza, 8) from autoriza_te
			where cod_item_nota_venta = @ve_cod_item_nota_venta	
		
		else if (@ve_dato_solicitado = 'USUARIO_AUTORIZA')
			select @vl_dato_solicitado = nom_usuario from autoriza_te at, usuario u
			where cod_item_nota_venta = @ve_cod_item_nota_venta and
				u.cod_usuario = at.cod_usuario	
		
		set @res = @vl_dato_solicitado;
	end 

	return @res;
END