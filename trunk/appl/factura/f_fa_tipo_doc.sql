------------------ [f_fa_tipo_doc] ----------------
-- Retorna el TIPO DE FACTURA ( EXENTA Ó NORMAL)
CREATE function [dbo].[f_fa_tipo_doc](@ve_cod_factura numeric)
RETURNS varchar(10) --  retorna el tipo de fa como text
AS
BEGIN
	declare @tipo_fa	varchar(10)
			,@vl_porc_iva T_PORCENTAJE

	set @tipo_fa = 'NORMAL'
				
	select	@vl_porc_iva = porc_iva
	from	factura
	where	cod_factura = @ve_cod_factura

	if (@vl_porc_iva = 0)
		set @tipo_fa = 'EXENTA'

	return @tipo_fa;
END
go