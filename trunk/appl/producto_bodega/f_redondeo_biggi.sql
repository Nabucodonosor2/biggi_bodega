------------------------
-- si se modifica esta funcion tambien debe modificarse en 
-- redondeo_biggi de producto.js
------------------------
CREATE FUNCTION [dbo].[f_redondeo_biggi](@ve_base DECIMAL(10,2), @ve_fac_int DECIMAL(10,2))
RETURNS DECIMAL(10,2)
AS
BEGIN
	declare @ve_precio_vta_sugerido DECIMAL(10,2)	
	set @ve_precio_vta_sugerido = (@ve_base * @ve_fac_int);

	if (@ve_precio_vta_sugerido < 1000)
		set @ve_precio_vta_sugerido = round(@ve_precio_vta_sugerido,-1);		
	else if (@ve_precio_vta_sugerido < 20000)
		set @ve_precio_vta_sugerido = round(@ve_precio_vta_sugerido,-2);		
	else if (@ve_precio_vta_sugerido < 100000)
		set @ve_precio_vta_sugerido = round(@ve_precio_vta_sugerido,-3);		
	else
		set @ve_precio_vta_sugerido = round(@ve_precio_vta_sugerido*2,-4)/2;		
			
return @ve_precio_vta_sugerido;
END	
go