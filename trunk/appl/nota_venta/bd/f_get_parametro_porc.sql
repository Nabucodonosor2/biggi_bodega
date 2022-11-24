---------------------f_get_parametro_porc------------------
-- esta funcion permite retornar los valores de porcentaje de descuento que se encuentran en la tabla 
ALTER FUNCTION f_get_parametro_porc(@ve_tipo_parametro_porc varchar(10), @ve_fecha_inicio_vigencia datetime)	
RETURNS numeric(10,2)
AS
BEGIN
declare @valor_porc varchar(100)
		
	select top 1 @valor_porc = porc_parametro 
	from parametro_porc
	where tipo_parametro = @ve_tipo_parametro_porc and
			fecha_inicio_vigencia <= @ve_fecha_inicio_vigencia
	order by fecha_inicio_vigencia desc	
	
return isnull (@valor_porc, 0);
end 
