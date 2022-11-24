---[f_prod_get_costo_base]---CAMBIAR TAMBIEN EN ECLIPSE-----
CREATE FUNCTION [dbo].[f_prod_get_costo_base](@ve_cod_producto varchar(50))
RETURNS numeric
AS
BEGIN
	declare @ve_costo_base numeric	
	set @ve_costo_base = 1;
			
return @ve_costo_base;
END
go