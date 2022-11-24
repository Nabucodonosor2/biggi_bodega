ALTER FUNCTION [dbo].[f_oc_get_saldo_sin_faprov](@ve_cod_orden_compra numeric)
RETURNS numeric
AS
BEGIN
	declare @vl_total_con_iva	T_PRECIO,
			@vl_total_asignado	T_PRECIO,
			@vl_total_espera	T_PRECIO,
			@kl_estado_emitida numeric,
			@kl_estado_anulada numeric,
			@kl_estado_cerrada numeric,
			@kl_estado_autorizado numeric
	
	set @kl_estado_emitida = 1;
	set @kl_estado_cerrada = 3
	set @kl_estado_autorizado = 4
	set @kl_estado_anulada = 5;

	select @vl_total_con_iva = total_con_iva
	from   orden_compra
	where  cod_orden_compra  = @ve_cod_orden_compra and
			cod_estado_orden_compra in(@kl_estado_emitida, @kl_estado_cerrada, @kl_estado_autorizado)
	
	select @vl_total_asignado = isnull(sum(monto_asignado),0)
	from item_faprov it, faprov f
	where it.cod_doc = @ve_cod_orden_compra and
		f.cod_faprov = it.cod_faprov and
		f.origen_faprov = 'ORDEN_COMPRA' and
		f.cod_estado_faprov not in (@kl_estado_anulada)

	set @vl_total_espera = @vl_total_con_iva - @vl_total_asignado 

	return isnull(@vl_total_espera,0);
END
go