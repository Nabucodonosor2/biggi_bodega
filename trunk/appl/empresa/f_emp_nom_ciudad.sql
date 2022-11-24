-----------------------------  f_emp_nom_ciudad ------------------
alter FUNCTION f_emp_nom_ciudad(@ve_cod_empresa numeric)
RETURNS varchar(100)
AS
-- Obtiene el nom_ciudad de la sucursal marcada como de FACTURACION
BEGIN
	declare 
		@vl_nom_ciudad		varchar(100)
	
	select TOP 1 @vl_nom_ciudad = C.NOM_CIUDAD
	from 	SUCURSAL S, CIUDAD C
	where 	S.COD_EMPRESA = @ve_cod_empresa
	  and	S.DIRECCION_FACTURA = 'S'
	  and	C.COD_CIUDAD = S.COD_CIUDAD

	return @vl_nom_ciudad
END