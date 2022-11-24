-------------------f_pago_faprov_get_por_asignar---------------
/* esta funcion se utiliza en pago_faprov,
 lo que hace es mostar el monto que queda por asignar sobre ese pago_faprov,
 para poder asignar montos la faprov debe estar aprobada = Cod_estado_faprov = 2*/
ALTER FUNCTION [dbo].[f_pago_faprov_get_por_asignar](@ve_cod_faprov numeric)
RETURNS numeric
AS
BEGIN
	------------------------------
	/* VMC 15-04-2014 para mejorar la velocidad se crea tabgla FAPROV_SALDO_CERO
	 donde estan los cod_faprov con saldo cero
	 
	insert into FAPROV_SALDO_CERO
	SELECT f.COD_FAPROV
	FROM 	FAPROV F
	where dbo.f_pago_faprov_get_por_asignar(F.COD_FAPROV) = 0
	and f.COD_FAPROV not in (select COD_FAPROV from FAPROV_SALDO_CERO)
	
	*/
	declare
		@vl_count	numeric
		
	select @vl_count = count(*)
	from FAPROV_SALDO_CERO
	where COD_FAPROV = @ve_cod_faprov
	
	if (@vl_count = 1) begin
		return 0
	end
	------------------------------



	declare @vl_total_con_iva	T_PRECIO,
			@vl_total_asignado	T_PRECIO,
			@vl_total_espera	T_PRECIO,
			@kl_estado_aprobada numeric,
			@kl_estado_faprov_impresa numeric,
			@kl_estado_faprov_emitida numeric			

	set @kl_estado_aprobada = 2
	set @kl_estado_faprov_emitida = 1
	set @kl_estado_faprov_impresa = 2

	select @vl_total_con_iva = total_con_iva  - dbo.f_pago_faprov_get_monto_ncprov(@ve_cod_faprov) 
	from   faprov 
	where  cod_faprov  = @ve_cod_faprov and
			cod_estado_faprov = @kl_estado_aprobada
			
	select @vl_total_asignado = isnull(sum(monto_asignado),0)
	from pago_faprov_faprov p, pago_faprov f
	where p.cod_faprov = @ve_cod_faprov and
		f.cod_pago_faprov =  p.cod_pago_faprov  and
		f.cod_estado_pago_faprov in (@kl_estado_faprov_emitida, @kl_estado_faprov_impresa)

	set @vl_total_espera = @vl_total_con_iva - @vl_total_asignado 

	return isnull (@vl_total_espera,0);
END	
