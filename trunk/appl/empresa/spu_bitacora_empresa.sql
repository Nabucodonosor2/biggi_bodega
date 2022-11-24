-------------------- spu_bitacora_empresa---------------------------------
-- Solo permite INSERT'
CREATE PROCEDURE [dbo].[spu_bitacora_empresa](@ve_operacion varchar(20), @ve_nom_bitacora_empresa varchar(100), @ve_cod_usuario numeric, @ve_cod_empresa numeric)
AS
BEGIN
	if (@ve_operacion='INSERT') 
		begin
			insert into bitacora_empresa (nom_bitacora_empresa, fecha_bitacora_empresa, cod_usuario, cod_empresa)
			values (@ve_nom_bitacora_empresa, getdate(), @ve_cod_usuario, @ve_cod_empresa)
		end 
END
go
