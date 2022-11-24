-- Dado un tipo de dodumento y el codigo de usuario obtiene el siguiente nro que le corresponde.
 -- Si no existen nros disponibles asignados retorna -1
CREATE FUNCTION [dbo].[f_get_nro_doc_sii](@ve_cod_tipo_doc_sii numeric, @ve_cod_usuario numeric)
RETURNS numeric
AS
BEGIN
	declare @nro_doc_sii numeric
	

	set @nro_doc_sii = -1
	select top 1 @nro_doc_sii = dbo.f_asig_get_nro_doc_sii(cod_asig_nro_doc_sii)
	from   asig_nro_doc_sii
	where  cod_usuario_receptor = @ve_cod_usuario and
		   cod_tipo_doc_sii = @ve_cod_tipo_doc_sii and
		   dbo.f_asig_cant_disponible(cod_asig_nro_doc_sii) > 0
	order by cod_asig_nro_doc_sii


	return @nro_doc_sii;
END	
go