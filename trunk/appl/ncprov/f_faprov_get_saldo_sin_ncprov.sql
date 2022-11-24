-----------------dbo.f_faprov_get_saldo_sin_ncprov------------
ALTER FUNCTION [dbo].[f_faprov_get_saldo_sin_ncprov](@ve_cod_faprov numeric)
RETURNS numeric
AS
BEGIN
	declare @vl_total_faprov	T_PRECIO,
			@vl_total_pago_faprov	T_PRECIO,
			@vl_total_ncprov 	T_PRECIO,
			@vl_total_espera	T_PRECIO,
			@kl_estado_faprov_emitida numeric,
			@kl_estado_faprov_impresa numeric,
			@kl_estado_ncprov_anulada numeric
	
	set @kl_estado_faprov_emitida = 1
	set @kl_estado_faprov_impresa = 2
	set @kl_estado_ncprov_anulada = 4
			
	--total faprov
	select @vl_total_faprov = total_con_iva
	from   faprov
	where  cod_faprov  = @ve_cod_faprov and
			cod_estado_faprov = @kl_estado_faprov_impresa 
	
	--total pago faprov
	select @vl_total_pago_faprov = isnull(sum(monto_asignado),0)
	from pago_faprov_faprov p, pago_faprov f
	where p.cod_faprov = @ve_cod_faprov and
		f.cod_pago_faprov =  p.cod_pago_faprov  and
		f.cod_estado_pago_faprov in (@kl_estado_faprov_emitida, @kl_estado_faprov_impresa)
		
	--total nc faprov
	select @vl_total_ncprov = isnull(sum(monto_asignado),0)
	from ncprov_faprov nf, ncprov n    
	where nf.cod_faprov = @ve_cod_faprov and
	nf.cod_ncprov = n.cod_ncprov and
	n.cod_estado_ncprov <> @kl_estado_ncprov_anulada

	set @vl_total_espera = @vl_total_faprov - @vl_total_ncprov - @vl_total_pago_faprov  

	return isnull(@vl_total_espera,0);
END	
go