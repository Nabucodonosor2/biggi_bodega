------------------f_nv_get_first_proveedor----------------
--	Esta función retorna el código del proveedor de un determinado equipo,
-- 	Donde el proveedor sea el primero por orden. 

CREATE FUNCTION [dbo].[f_nv_get_first_proveedor](@ve_cod_producto varchar(30))
RETURNS NUMERIC
AS
BEGIN
DECLARE @cod_first_proveedor NUMERIC 

select top (1)  
	@cod_first_proveedor = COD_EMPRESA 
from PRODUCTO_PROVEEDOR
where COD_PRODUCTO = @ve_cod_producto
	and ELIMINADO = 'N'
order by ORDEN asc

RETURN @cod_first_proveedor ;
END		
go