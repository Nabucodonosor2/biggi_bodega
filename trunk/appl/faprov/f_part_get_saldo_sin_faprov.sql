-----------------dbo.f_part_get_saldo_sin_faprov-------------------
CREATE FUNCTION [dbo].[f_part_get_saldo_sin_faprov](@ve_cod_participacion numeric)
RETURNS numeric
AS
BEGIN
	declare @vl_total_con_iva	T_PRECIO,
			@vl_total_asignado	T_PRECIO,
			@vl_total_espera	T_PRECIO,
			@kl_estado_confirmada numeric,
			@kl_estado_anulada numeric

	set @kl_estado_confirmada = 2;
	set @kl_estado_anulada = 5;

	select @vl_total_con_iva = total_con_iva
	from   participacion
	where  cod_participacion  = @ve_cod_participacion and
			cod_estado_participacion = @kl_estado_confirmada
	
	select @vl_total_asignado = isnull(sum(monto_asignado),0)
	from item_faprov it, faprov f
	where it.cod_doc = @ve_cod_participacion and
		f.cod_faprov = it.cod_faprov and
		f.origen_faprov = 'PARTICIPACION' and
		f.cod_estado_faprov not in (@kl_estado_anulada)


	set @vl_total_espera = @vl_total_con_iva - @vl_total_asignado 

	return isnull(@vl_total_espera,0);
END	
go