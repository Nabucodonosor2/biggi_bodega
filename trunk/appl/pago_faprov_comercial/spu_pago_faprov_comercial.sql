CREATE PROCEDURE spu_pago_faprov_comercial(@ve_cod_usuario	numeric(10))
AS
BEGIN
	DELETE PAGO_FAPROV_FAPROV_COM
	WHERE COD_USUARIO_WS = @ve_cod_usuario
	
	DELETE PAGO_FAPROV_COMERCIAL
	WHERE COD_USUARIO_WS = @ve_cod_usuario
END