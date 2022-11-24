--------------------f_pago_faprov_get_pago_ant-----------------
/*esta funcion se utiliza en el pago_faprov
 lo que hace es mostrar los pagos anteriores que ha tenido esa faprov
 */

CREATE FUNCTION [dbo].[f_pago_faprov_get_pago_ant](@ve_cod_faprov numeric)
RETURNS numeric
AS
BEGIN
	declare @vl_res			T_PRECIO,
			@vl_por_pagar	T_PRECIO,
			@vl_asignado	T_PRECIO
	
	select @vl_por_pagar = total_con_iva - dbo.f_pago_faprov_get_monto_ncprov(@ve_cod_faprov) 
	from   faprov 
	where  cod_faprov  = @ve_cod_faprov

	select @vl_asignado = monto_asignado
	from   pago_faprov_faprov 
	where  cod_faprov  = @ve_cod_faprov

	set @vl_res = (@vl_por_pagar - @vl_asignado)- dbo.f_pago_faprov_get_por_asignar(@ve_cod_faprov) + @vl_asignado

	return isnull (@vl_res,0);

END	
go