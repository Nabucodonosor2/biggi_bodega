CREATE PROCEDURE [dbo].[spu_acuse_dte](
@ve_operacion		varchar(20)
,@ve_emisor			varchar(100)=NULL
,@ve_receptor		varchar(100)=NULL
,@ve_nro_documento	numeric=NULL
)
AS
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into ACUSE_DTE (FECHA, EMISOR, RECEPTOR, NRO_DOCUMENTO)
		values (GETDATE(),@ve_emisor,@ve_receptor,@ve_nro_documento)
	end 
END
go