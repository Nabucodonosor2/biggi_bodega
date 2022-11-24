-----------------f_pago_faprov_get_monto_ncprov------------------
/* esta funcion se utiliza en el pago_faprov, 
 lo que hace es traer el monto que se ha cancelado en la NC
 siempre y cuando el estado de la NC este aprobada 
 
 22-04-2014 se cambio la funcionalidad el codigo comentado es lo que hacia antes
 */
alter FUNCTION [dbo].[f_pago_faprov_get_monto_ncprov](@ve_cod_faprov numeric)
RETURNS numeric
AS
BEGIN
	declare @vl_asignado	T_PRECIO
	
	/*
	select @vl_asignado = nf.monto_asignado
	from   ncprov_faprov nf, faprov f, ncprov n
	where  f.cod_faprov  = @ve_cod_faprov and
			f.cod_faprov = nf.cod_faprov and
			n.cod_ncprov = nf.cod_ncprov AND
			n.cod_estado_ncprov = 2
	*/
			 
	select @vl_asignado = isnull(sum(n.MONTO_ASIGNADO), 0)
	from NCPROV_USADA n, NCPROV_PAGO_FAPROV npp, pago_faprov pf
	where n.COD_FAPROV = @ve_cod_faprov
	  and npp.COD_NCPROV_PAGO_FAPROV = n.COD_NCPROV_PAGO_FAPROV
	  and pf.cod_pago_faprov = npp.cod_pago_faprov
	  and pf.cod_estado_pago_faprov in (1,2)

	return isnull (@vl_asignado,0);
END	
go
