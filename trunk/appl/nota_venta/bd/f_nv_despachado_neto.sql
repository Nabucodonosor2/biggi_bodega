-- Retorna el monto despachado NETO, esta funcion es usada en los informes
ALTER FUNCTION [dbo].[f_nv_despachado_neto](@ve_cod_nota_venta numeric)
RETURNS T_PRECIO
AS
BEGIN
DECLARE @despachado_neto	numeric

select @despachado_neto = isnull(sum((it.cantidad - dbo.f_nv_cant_por_despachar(it.cod_item_nota_venta, default)) * it.precio) * 
								 case nv.subtotal when 0 then 1 else nv.total_neto/nv.subtotal end, 0)
from item_nota_venta it, nota_venta nv
where nv.cod_nota_venta = @ve_cod_nota_venta
  and it.cod_nota_venta = nv.cod_nota_venta 
group by nv.total_neto, nv.subtotal


	return @despachado_neto	   
END
