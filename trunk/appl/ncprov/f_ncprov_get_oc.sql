CREATE FUNCTION [dbo].[f_ncprov_get_oc](@ve_cod_ncprov numeric)
RETURNS varchar(30)
AS
BEGIN
	declare @nro_doc	varchar(30),
			@cant_doc	numeric

		SELECT top 1 @nro_doc =  convert(varchar(10), IFA.COD_DOC) 
		FROM ITEM_FAPROV IFA, NCPROV_FAPROV NCF, NCPROV NC
		WHERE NC.COD_NCPROV = @ve_cod_ncprov
		AND NC.COD_NCPROV = NCF.COD_NCPROV
		AND	  NCF.COD_FAPROV = IFA.COD_FAPROV
		ORDER BY NC.COD_NCPROV DESC
		
		SELECT	@cant_doc = count(*)
		FROM ITEM_FAPROV IFA, NCPROV_FAPROV NCF, NCPROV NC
		WHERE NC.COD_NCPROV = @ve_cod_ncprov
		AND NC.COD_NCPROV = NCF.COD_NCPROV
		AND	  NCF.COD_FAPROV = IFA.COD_FAPROV
	
	if(@cant_doc > 1)
		set @nro_doc = @nro_doc + '+'

	return @nro_doc;
END