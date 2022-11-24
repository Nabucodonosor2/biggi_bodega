-------------------- spu_factura_rechazada ---------------------------------
CREATE PROCEDURE spu_factura_rechazada(@ve_operacion				varchar(20)
									  ,@ve_cod_factura_rechazada	numeric
									  ,@ve_cod_factura				numeric=NULL
									  ,@ve_resuelta					varchar(1)=NULL
									  ,@ve_obs						text=NULL
									  ,@ve_cod_usuario				numeric=NULL)
AS
BEGIN
	DECLARE
		@vl_resuelta		varchar(1),
		@vl_fecha_rechazo	datetime
		
	IF (@ve_operacion = 'INSERT')BEGIN
		SET @vl_fecha_rechazo = GETDATE()
		
		INSERT INTO FACTURA_RECHAZADA (COD_FACTURA
									  ,FECHA_RECHAZO
									  ,RESUELTA)
								VALUES(@ve_cod_factura
									  ,@vl_fecha_rechazo
									  ,'N')	  
	END 
	IF (@ve_operacion = 'UPDATE')BEGIN
		SELECT @vl_resuelta = RESUELTA
		FROM FACTURA_RECHAZADA
		WHERE COD_FACTURA_RECHAZADA = @ve_cod_factura_rechazada
		
		if(@vl_resuelta = 'N' AND @ve_resuelta = 'S')
			UPDATE FACTURA_RECHAZADA
			SET RESUELTA				= @ve_resuelta
			   ,OBS						= @ve_obs
			   ,FECHA_RESUELTA			= GETDATE()
			   ,COD_USUARIO_RESUELTA	= @ve_cod_usuario
			WHERE COD_FACTURA_RECHAZADA = @ve_cod_factura_rechazada
		else
			UPDATE FACTURA_RECHAZADA
			SET RESUELTA				= @ve_resuelta
			   ,OBS						= @ve_obs
			WHERE COD_FACTURA_RECHAZADA = @ve_cod_factura_rechazada
	END
END