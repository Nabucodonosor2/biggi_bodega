------------------  sp_change_password  ----------------------------
alter PROCEDURE [dbo].[sp_change_password](	@ve_clave_actual varchar(100),
											@ve_clave_nueva varchar(100),
											@ve_clave_confirmacion varchar(100),
											@ve_cod_usuario numeric)

AS
BEGIN
	UPDATE	USUARIO
	SET		PASSWORD	= @ve_clave_nueva
			,FECHA_PASSWORD = GETDATE()
	WHERE	COD_USUARIO = @ve_cod_usuario;
END
go